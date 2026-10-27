// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"archive/zip"
	"embed"
	"encoding/base64"
	"encoding/binary"
	"encoding/pem"
	"errors"
	"os"
	"path/filepath"
	"strings"
)

//go:embed client/*
var clientFiles embed.FS

// Un únic executable per als clients Windows. Només incorpora dades públiques.
func clientExecutable(executable []byte, authority, fingerprint string, root []byte) ([]byte, error) {
	cert, _ := pem.Decode(root)
	if cert == nil || cert.Type != "CERTIFICATE" {
		return nil, errors.New("certificat públic invàlid")
	}
	payload := []byte("https://" + authority + "/\n" + fingerprint + "\n" + base64.StdEncoding.EncodeToString(cert.Bytes))
	if len(payload) > 65536 {
		return nil, errors.New("certificat públic massa gran")
	}
	out := append([]byte(nil), executable...)
	out = append(out, payload...)
	out = binary.LittleEndian.AppendUint32(out, uint32(len(payload)))
	return append(out, []byte("COMPAS-CONNEXIO-1")...), nil
}

func clientBundle(dir, authority, fingerprint string, root []byte) error {
	clientDir := filepath.Join(dir, "clients")
	if err := os.MkdirAll(clientDir, 0755); err != nil {
		return err
	}
	f, err := os.Create(filepath.Join(dir, "Client-Compas.zip"))
	if err != nil {
		return err
	}
	z := zip.NewWriter(f)
	closeAll := func() error {
		e := z.Close()
		e2 := f.Close()
		if e != nil {
			return e
		}
		return e2
	}
	add := func(name string, data []byte) error {
		if e := os.WriteFile(filepath.Join(clientDir, name), data, 0644); e != nil {
			return e
		}
		w, e := z.Create(name)
		if e == nil {
			_, e = w.Write(data)
		}
		return e
	}
	entries, err := clientFiles.ReadDir("client")
	if err != nil {
		closeAll()
		return err
	}
	for _, entry := range entries {
		b, e := clientFiles.ReadFile("client/" + entry.Name())
		if e != nil {
			closeAll()
			return e
		}
		text := strings.NewReplacer("@URL@", "https://"+authority+"/", "@EMPREMTA@", fingerprint, "@ID@", fingerprint[:16]).Replace(string(b))
		if e := os.WriteFile(filepath.Join(clientDir, entry.Name()), []byte(text), 0644); e != nil {
			closeAll()
			return e
		}
		h := &zip.FileHeader{Name: entry.Name(), Method: zip.Deflate}
		h.SetMode(0644)
		if strings.HasSuffix(entry.Name(), ".sh") {
			h.SetMode(0755)
		}
		w, e := z.CreateHeader(h)
		if e == nil {
			_, e = w.Write([]byte(text))
		}
		if e != nil {
			closeAll()
			return e
		}
	}
	if err = add("compas-ca.crt", root); err != nil {
		closeAll()
		return err
	}
	if err = add("connexio.txt", []byte("https://"+authority+"/\n"+fingerprint+"\n")); err != nil {
		closeAll()
		return err
	}
	exe, e := os.Executable()
	if e == nil {
		b, e := os.ReadFile(filepath.Join(filepath.Dir(exe), "Connecta-Compas.exe"))
		if e == nil {
			b, e = clientExecutable(b, authority, fingerprint, root)
			if e != nil {
				closeAll()
				return e
			}
			if e = os.WriteFile(filepath.Join(dir, "Connecta-Compas.exe"), b, 0644); e != nil {
				closeAll()
				return e
			}
			if err = add("Connecta-Compas.exe", b); err != nil {
				closeAll()
				return err
			}
		}
	}
	return closeAll()
}
