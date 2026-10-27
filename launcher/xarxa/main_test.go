// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"crypto/tls"
	"crypto/x509"
	"encoding/pem"
	"io"
	"net/http"
	"net/http/httptest"
	"net/url"
	"os"
	"path/filepath"
	"strings"
	"testing"
)

func TestCertificates(t *testing.T) {
	d := t.TempDir()
	pair, root, err := certificates(d, "compas.local")
	if err != nil {
		t.Fatal(err)
	}
	pool := x509.NewCertPool()
	pool.AppendCertsFromPEM(root)
	leaf, _ := x509.ParseCertificate(pair.Certificate[0])
	if _, err = leaf.Verify(x509.VerifyOptions{DNSName: "compas.local", Roots: pool}); err != nil {
		t.Fatal(err)
	}
	if _, err = leaf.Verify(x509.VerifyOptions{DNSName: "altre.local", Roots: pool}); err == nil {
		t.Fatal("accepta nom diferent")
	}
	_, again, err := certificates(d, "compas.local")
	if err != nil || string(root) != string(again) {
		t.Fatal("la CA no es conserva", err)
	}
	next, same, err := certificates(d, "entitat.local")
	if err != nil || string(same) != string(root) {
		t.Fatal("canviar el nom canvia la CA", err)
	}
	nl, _ := x509.ParseCertificate(next.Certificate[0])
	if nl.VerifyHostname("entitat.local") != nil {
		t.Fatal("no renova el nom")
	}
	os.Remove(filepath.Join(d, "compas-ca.key"))
	if _, _, err = certificates(d, "compas.local"); err == nil {
		t.Fatal("regenera una CA incompleta")
	}
	for _, name := range []string{"compas.example", "../compas.local", "compàs.local", "-compas.local", "compas.local.evil"} {
		if _, _, err = certificates(d, name); err == nil {
			t.Fatal("nom invàlid acceptat", name)
		}
	}
}

func TestProxyAndTLS(t *testing.T) {
	token := strings.Repeat("a", 48)
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Host != "compas.local" || r.Header.Get("X-Compas-Proxy-Token") != token || r.Header.Get("X-Compas-Client") != "127.0.0.1" || r.Header.Get("X-Compas-Local") != "1" {
			t.Error("metadades no validades", r.Header)
		}
		for _, h := range []string{"Forwarded", "X-Forwarded-For", "X-Compas-Token"} {
			if r.Header.Get(h) != "" {
				t.Error("capçalera falsa conservada", h)
			}
		}
		io.WriteString(w, "correcte")
	}))
	defer backend.Close()
	u, _ := url.Parse(backend.URL)
	d := t.TempDir()
	pair, root, err := certificates(d, "compas.local")
	if err != nil {
		t.Fatal(err)
	}
	h := proxyHandler(u, "compas.local", "compas.local", token, root)
	s := httptest.NewUnstartedServer(h)
	s.TLS = &tls.Config{MinVersion: tls.VersionTLS12, Certificates: []tls.Certificate{pair}}
	s.StartTLS()
	defer s.Close()
	pool := x509.NewCertPool()
	pool.AppendCertsFromPEM(root)
	client := &http.Client{Transport: &http.Transport{TLSClientConfig: &tls.Config{ServerName: "compas.local", RootCAs: pool}}}
	req, _ := http.NewRequest("GET", s.URL+"/?r=inici", nil)
	req.Host = "compas.local"
	for _, name := range []string{"Forwarded", "X-Forwarded-For", "X-Compas-Token", "X-Compas-Client", "X-Compas-Proxy-Token", "X-Compas-Local"} {
		req.Header.Set(name, "atacant")
	}
	r, err := client.Do(req)
	if err != nil {
		t.Fatal(err)
	}
	body, _ := io.ReadAll(r.Body)
	r.Body.Close()
	if string(body) != "correcte" {
		t.Fatal(string(body))
	}
	req.Host = "atacant.local"
	r, err = client.Do(req)
	if err != nil {
		t.Fatal(err)
	}
	r.Body.Close()
	if r.StatusCode != 421 {
		t.Fatal("Host no restringit")
	}
	req.Host = "compas.local"
	req.URL.Path = "/compas-ca.crt"
	r, err = client.Do(req)
	if err != nil {
		t.Fatal(err)
	}
	body, _ = io.ReadAll(r.Body)
	r.Body.Close()
	block, _ := pem.Decode(body)
	if block == nil || block.Type != "CERTIFICATE" {
		t.Fatal("no servix certificat públic")
	}
	bad := &http.Client{Transport: &http.Transport{TLSClientConfig: &tls.Config{ServerName: "compas.local"}}}
	if _, err = bad.Do(req); err == nil {
		t.Fatal("TLS sense confiança acceptat")
	}
	for _, remote := range []string{"8.8.8.8:1234", "[2001:4860::1]:1234"} {
		q := httptest.NewRequest("GET", "https://compas.local/", nil)
		q.RemoteAddr = remote
		q.Host = "compas.local"
		w := httptest.NewRecorder()
		h.ServeHTTP(w, q)
		if w.Code != 403 {
			t.Fatal("IP pública acceptada")
		}
	}
}

func TestRemoteMetadataAliases(t *testing.T) {
	token := strings.Repeat("b", 48)
	ip := "10.254.254.254"
	if localAddresses()[ip] {
		ip = "192.168.254.254"
	}
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Header.Get("X-Compas-Client") != ip || r.Header.Get("X-Compas-Local") != "0" || r.Header.Get("X-Compas-Proxy-Token") != token {
			t.Error("identitat remota falsejada", r.Header)
		}
		for key := range r.Header {
			normal := strings.NewReplacer("_", "-", ".", "-").Replace(strings.ToLower(key))
			if strings.HasPrefix(normal, "x-compas-") && key != "X-Compas-Client" && key != "X-Compas-Local" && key != "X-Compas-Proxy-Token" {
				t.Error("alias reservat no eliminat", key)
			}
		}
		io.WriteString(w, "correcte")
	}))
	defer backend.Close()
	u, _ := url.Parse(backend.URL)
	handler := proxyHandler(u, "compas.local", "compas.local", token, nil)
	req := httptest.NewRequest("GET", "https://compas.local/", nil)
	req.RemoteAddr = ip + ":12345"
	req.Host = "compas.local"
	for _, prefix := range []string{"X-Compas-", "X_Compas_", "X.Compas.", "X-Compas_"} {
		req.Header.Set(prefix+"Local", "1")
		req.Header.Set(prefix+"Client", "127.0.0.1")
		req.Header.Set(prefix+"Proxy-Token", "fals")
		req.Header.Set(prefix+"Token", "fals")
	}
	w := httptest.NewRecorder()
	handler.ServeHTTP(w, req)
	if w.Code != 200 || w.Body.String() != "correcte" {
		t.Fatal("petició remota no processada correctament", w.Code, w.Body.String())
	}
}
