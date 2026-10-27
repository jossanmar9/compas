// Compàs: servidor HTTPS i descobriment local, sense serveis externs.
// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"context"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/sha256"
	"crypto/tls"
	"crypto/x509"
	"crypto/x509/pkix"
	"encoding/hex"
	"encoding/pem"
	"errors"
	"flag"
	"fmt"
	"log"
	"math/big"
	"net"
	"net/http"
	"net/http/httputil"
	"net/url"
	"os"
	"os/signal"
	"path/filepath"
	"regexp"
	"strings"
	"sync"
	"syscall"
	"time"

	"github.com/pion/mdns/v2"
	"golang.org/x/net/ipv4"
)

var validName = regexp.MustCompile(`^[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local$`)

func private(ip net.IP) bool {
	v := ip.To4()
	return v != nil && (v[0] == 10 || v[0] == 172 && v[1] >= 16 && v[1] <= 31 || v[0] == 192 && v[1] == 168)
}

func localAddresses() map[string]bool {
	result := map[string]bool{"127.0.0.1": true, "::1": true}
	ifaces, _ := net.Interfaces()
	for _, iface := range ifaces {
		if iface.Flags&net.FlagUp == 0 {
			continue
		}
		addrs, _ := iface.Addrs()
		for _, addr := range addrs {
			ip, _, err := net.ParseCIDR(addr.String())
			if err == nil && private(ip) {
				result[ip.String()] = true
			}
		}
	}
	return result
}

func serial() *big.Int {
	n, err := rand.Int(rand.Reader, new(big.Int).Lsh(big.NewInt(1), 128))
	if err != nil {
		panic(err)
	}
	return n
}
func savePEM(path, kind string, bytes []byte, mode os.FileMode) error {
	// Fitxer temporal al mateix volum: mai deixem una clau parcial escrita.
	f, err := os.OpenFile(path+".nou", os.O_CREATE|os.O_TRUNC|os.O_WRONLY, mode)
	if err != nil {
		return err
	}
	if err = pem.Encode(f, &pem.Block{Type: kind, Bytes: bytes}); err != nil {
		f.Close()
		return err
	}
	if err = f.Close(); err != nil {
		return err
	}
	// Windows no substitueix un destí existent amb Rename.
	if err = os.Remove(path); err != nil && !os.IsNotExist(err) {
		return err
	}
	return os.Rename(path+".nou", path)
}
func readKey(path string) (*ecdsa.PrivateKey, error) {
	b, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}
	p, _ := pem.Decode(b)
	if p == nil {
		return nil, errors.New("clau PEM invàlida")
	}
	k, err := x509.ParseECPrivateKey(p.Bytes)
	return k, err
}

// Una CA per instal·lació; el certificat del servidor es renova abans de caducar.
func certificates(dir, name string) (tls.Certificate, []byte, error) {
	var empty tls.Certificate
	if !validName.MatchString(name) {
		return empty, nil, errors.New("nom local invàlid")
	}
	if err := os.MkdirAll(dir, 0700); err != nil {
		return empty, nil, err
	}
	if err := protect(dir); err != nil {
		return empty, nil, fmt.Errorf("no es poden protegir les claus privades: %w", err)
	}
	rootPath, keyPath := filepath.Join(dir, "compas-ca.crt"), filepath.Join(dir, "compas-ca.key")
	rootPEM, rootErr := os.ReadFile(rootPath)
	key, keyErr := readKey(keyPath)
	var root *x509.Certificate
	if rootErr == nil {
		block, _ := pem.Decode(rootPEM)
		if block == nil {
			return empty, nil, errors.New("certificat arrel invàlid")
		}
		var err error
		root, err = x509.ParseCertificate(block.Bytes)
		if err != nil {
			return empty, nil, err
		}
	}
	if os.IsNotExist(rootErr) && os.IsNotExist(keyErr) {
		var err error
		key, err = ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
		if err != nil {
			return empty, nil, err
		}
		root = &x509.Certificate{SerialNumber: serial(), Subject: pkix.Name{CommonName: "Compàs · autoritat local"}, NotBefore: time.Now().Add(-time.Hour), NotAfter: time.Now().AddDate(10, 0, 0), IsCA: true, BasicConstraintsValid: true, KeyUsage: x509.KeyUsageCertSign | x509.KeyUsageCRLSign, MaxPathLen: 0, MaxPathLenZero: true}
		der, err := x509.CreateCertificate(rand.Reader, root, root, &key.PublicKey, key)
		if err != nil {
			return empty, nil, err
		}
		kb, err := x509.MarshalECPrivateKey(key)
		if err != nil {
			return empty, nil, err
		}
		if err = savePEM(keyPath, "EC PRIVATE KEY", kb, 0600); err != nil {
			return empty, nil, err
		}
		if err = savePEM(rootPath, "CERTIFICATE", der, 0644); err != nil {
			return empty, nil, err
		}
		rootPEM, _ = os.ReadFile(rootPath)
		root, _ = x509.ParseCertificate(der)
	} else if rootErr != nil || keyErr != nil {
		return empty, nil, errors.New("falta el certificat o la clau arrel; no es regeneren automàticament per no perdre la confiança dels clients")
	}
	rootKey, ok := root.PublicKey.(*ecdsa.PublicKey)
	if !ok || !root.IsCA || time.Until(root.NotAfter) < 24*time.Hour || !rootKey.Equal(&key.PublicKey) {
		return empty, nil, errors.New("autoritat local caducada o inconsistent")
	}
	certPath, leafKeyPath := filepath.Join(dir, "servidor.crt"), filepath.Join(dir, "servidor.key")
	pair, err := tls.LoadX509KeyPair(certPath, leafKeyPath)
	renew := err != nil
	if !renew {
		leaf, e := x509.ParseCertificate(pair.Certificate[0])
		renew = e != nil
		if e == nil {
			renew = leaf.VerifyHostname(name) != nil || time.Until(leaf.NotAfter) < 30*24*time.Hour || leaf.CheckSignatureFrom(root) != nil
		}
	}
	if renew {
		leafKey, err := ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
		if err != nil {
			return empty, nil, err
		}
		leaf := &x509.Certificate{SerialNumber: serial(), Subject: pkix.Name{CommonName: name}, DNSNames: []string{name}, NotBefore: time.Now().Add(-time.Hour), NotAfter: time.Now().AddDate(0, 3, 0), KeyUsage: x509.KeyUsageDigitalSignature, ExtKeyUsage: []x509.ExtKeyUsage{x509.ExtKeyUsageServerAuth}}
		der, err := x509.CreateCertificate(rand.Reader, leaf, root, &leafKey.PublicKey, key)
		if err != nil {
			return empty, nil, err
		}
		kb, err := x509.MarshalECPrivateKey(leafKey)
		if err != nil {
			return empty, nil, err
		}
		if err = savePEM(leafKeyPath, "EC PRIVATE KEY", kb, 0600); err != nil {
			return empty, nil, err
		}
		if err = savePEM(certPath, "CERTIFICATE", der, 0644); err != nil {
			return empty, nil, err
		}
		pair, err = tls.LoadX509KeyPair(certPath, leafKeyPath)
		if err != nil {
			return empty, nil, err
		}
	}
	return pair, rootPEM, nil
}

func proxyHandler(backend *url.URL, name, authority, token string, root []byte) http.Handler {
	proxy := httputil.NewSingleHostReverseProxy(backend)
	original := proxy.Director
	proxy.Director = func(r *http.Request) {
		ip, _, _ := net.SplitHostPort(r.RemoteAddr)
		original(r)
		// No es confia en cap capçalera aportada pel navegador.
		for h := range r.Header {
			// PHP normalitza també punts i guions baixos en les variables HTTP_*.
			key := strings.NewReplacer("_", "-", ".", "-").Replace(strings.ToLower(h))
			if key == "forwarded" || strings.HasPrefix(key, "x-forwarded-") || strings.HasPrefix(key, "x-compas-") {
				delete(r.Header, h)
			}
		}
		r.Host = authority
		r.Header.Set("X-Compas-Proxy-Token", token)
		r.Header.Set("X-Compas-Client", ip)
		r.Header.Set("X-Compas-Local", "0")
		if localAddresses()[ip] {
			r.Header.Set("X-Compas-Local", "1")
		}
		// Impedix que ReverseProxy afija una capçalera X-Forwarded-For.
		r.Header["X-Forwarded-For"] = nil
	}
	proxy.Transport = &http.Transport{Proxy: nil, DialContext: (&net.Dialer{Timeout: 5 * time.Second}).DialContext, ResponseHeaderTimeout: 2 * time.Minute, MaxIdleConns: 16}
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		ip, _, err := net.SplitHostPort(r.RemoteAddr)
		if err != nil || !(private(net.ParseIP(ip)) || net.ParseIP(ip).IsLoopback()) {
			http.Error(w, "403", 403)
			return
		}
		if !strings.EqualFold(r.Host, authority) {
			http.Error(w, "421", 421)
			return
		}
		w.Header().Set("Strict-Transport-Security", "max-age=86400")
		if r.URL.Path == "/compas-ca.crt" {
			if r.Method != "GET" && r.Method != "HEAD" {
				http.Error(w, "405", 405)
				return
			}
			w.Header().Set("Content-Type", "application/x-pem-file")
			w.Header().Set("Cache-Control", "no-store")
			if r.Method != "HEAD" {
				w.Write(root)
			}
			return
		}
		proxy.ServeHTTP(w, r)
	})
}

func networkState() string {
	interfaces, _ := net.Interfaces()
	return fmt.Sprint(interfaces) + fmt.Sprint(localAddresses())
}

func discovery(ctx context.Context, name string) error {
	addr, _ := net.ResolveUDPAddr("udp4", mdns.DefaultAddressIPv4)
	options := func() ([]mdns.ServerOption, error) {
		var interfaces []net.Interface
		all, _ := net.Interfaces()
		for _, iface := range all {
			if iface.Flags&net.FlagUp == 0 || iface.Flags&net.FlagMulticast == 0 {
				continue
			}
			addrs, _ := iface.Addrs()
			for _, a := range addrs {
				ip, _, e := net.ParseCIDR(a.String())
				if e == nil && private(ip) {
					interfaces = append(interfaces, iface)
					break
				}
			}
		}
		if len(interfaces) == 0 {
			return nil, errors.New("no hi ha cap interfície de xarxa privada activa")
		}
		return []mdns.ServerOption{mdns.WithInterfaces(interfaces...), mdns.WithResponseTTL(10)}, nil
	}
	opts, err := options()
	if err != nil {
		return err
	}
	sock, err := net.ListenUDP("udp4", addr)
	if err != nil {
		return err
	}
	probe, err := mdns.NewServer(ipv4.NewPacketConn(sock), nil, opts...)
	if err != nil {
		sock.Close()
		return err
	}
	probeCtx, cancel := context.WithTimeout(ctx, time.Second)
	_, other, probeErr := probe.QueryAddr(probeCtx, name)
	cancel()
	probe.Close()
	sock.Close()
	if probeErr == nil && !localAddresses()[other.String()] {
		return fmt.Errorf("el nom %s ja està en ús; tria un altre nom local", name)
	}
	sock, err = net.ListenUDP("udp4", addr)
	if err != nil {
		return err
	}
	conn, err := mdns.NewServer(ipv4.NewPacketConn(sock), nil, append(opts, mdns.WithLocalNames(name))...)
	if err != nil {
		sock.Close()
		return err
	}
	// Torna a obrir les interfícies si canvia l'adreça DHCP o la connexió de xarxa.
	defer func() {
		if conn != nil {
			conn.Close()
		}
		if sock != nil {
			sock.Close()
		}
	}()
	before := networkState()
	ticker := time.NewTicker(5 * time.Second)
	defer ticker.Stop()
	for {
		select {
		case <-ctx.Done():
			return nil
		case <-ticker.C:
			now := networkState()
			if now != before {
				conn.Close()
				sock.Close()
				sock, err = net.ListenUDP("udp4", addr)
				if err != nil {
					return err
				}
				opts, err = options()
				if err != nil {
					return err
				}
				conn, err = mdns.NewServer(ipv4.NewPacketConn(sock), nil, append(opts, mdns.WithLocalNames(name))...)
				if err != nil {
					return err
				}
				before = now
			}
		}
	}
}

func run() error {
	dir := flag.String("dades", "", "Carpeta de certificats")
	name := flag.String("nom", "compas.local", "Nom .local")
	backendAddr := flag.String("backend", "127.0.0.1:8740", "Servidor PHP intern")
	port := flag.Int("port", 443, "Port HTTPS")
	prepare := flag.Bool("prepara", false, "Prepara certificats i mostra l'empremta")
	noMDNS := flag.Bool("sense-mdns", false, "Només per a proves o DNS administrat")
	flag.Parse()
	if *dir == "" || !validName.MatchString(*name) || *port < 1 || *port > 65535 {
		return errors.New("configuració HTTPS invàlida")
	}
	pair, root, err := certificates(*dir, *name)
	if err != nil {
		return err
	}
	block, _ := pem.Decode(root)
	hash := sha256.Sum256(block.Bytes)
	log.Printf("Certificat arrel SHA-256: %s", strings.ToUpper(hex.EncodeToString(hash[:])))
	authority := *name
	if *port != 443 {
		authority = net.JoinHostPort(*name, fmt.Sprint(*port))
	}
	if err = clientBundle(*dir, authority, strings.ToUpper(hex.EncodeToString(hash[:])), root); err != nil {
		return err
	}
	if err = os.WriteFile(filepath.Join(*dir, "empremta.txt"), []byte("https://"+authority+"/\nSHA-256: "+strings.ToUpper(hex.EncodeToString(hash[:]))+"\n"), 0644); err != nil {
		return err
	}
	if *prepare {
		return nil
	}
	token := os.Getenv("COMPAS_PROXY_TOKEN")
	if len(token) < 32 {
		return errors.New("falta el secret intern del proxy")
	}
	host, _, err := net.SplitHostPort(*backendAddr)
	if err != nil || host != "127.0.0.1" {
		return errors.New("PHP ha d'escoltar només en 127.0.0.1")
	}
	backend, _ := url.Parse("http://" + *backendAddr)
	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()
	listener, err := net.Listen("tcp4", fmt.Sprintf("0.0.0.0:%d", *port))
	if err != nil {
		return fmt.Errorf("no es pot obrir HTTPS: %w", err)
	}
	defer listener.Close()
	// Recàrrega i renovació automàtica del certificat, sense reiniciar PHP.
	var mu sync.Mutex
	renewed := time.Now()
	tlsConfig := &tls.Config{MinVersion: tls.VersionTLS12, GetCertificate: func(hello *tls.ClientHelloInfo) (*tls.Certificate, error) {
		if hello.ServerName != *name {
			return nil, errors.New("nom TLS no autoritzat")
		}
		mu.Lock()
		defer mu.Unlock()
		if time.Since(renewed) > 24*time.Hour {
			p, _, e := certificates(*dir, *name)
			if e != nil {
				return nil, e
			}
			pair = p
			renewed = time.Now()
		}
		return &pair, nil
	}}
	server := &http.Server{Handler: proxyHandler(backend, *name, authority, token, root), ReadHeaderTimeout: 10 * time.Second, IdleTimeout: 60 * time.Second, MaxHeaderBytes: 32 << 10, TLSConfig: tlsConfig}
	errorsCh := make(chan error, 2)
	if !*noMDNS {
		go func() { errorsCh <- discovery(ctx, *name) }()
	}
	go func() {
		log.Printf("Servidor: https://%s/", authority)
		errorsCh <- server.Serve(tls.NewListener(listener, tlsConfig))
	}()
	select {
	case err = <-errorsCh:
	case <-ctx.Done():
	}
	stop()
	shutdown, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()
	server.Shutdown(shutdown)
	if errors.Is(err, http.ErrServerClosed) {
		return nil
	}
	return err
}
func main() {
	if err := run(); err != nil {
		log.Print(err)
		os.Exit(1)
	}
}
