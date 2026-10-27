// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"bytes"
	"encoding/base64"
	"encoding/binary"
	"encoding/pem"
	"strings"
	"testing"
)

func TestClientExecutable(t *testing.T) {
	_, root, err := certificates(t.TempDir(), "compas-prova.local")
	if err != nil {
		t.Fatal(err)
	}
	original := []byte("MZ-programa-original")
	exe, err := clientExecutable(original, "compas-prova.local:8443", strings.Repeat("A", 64), root)
	if err != nil {
		t.Fatal(err)
	}
	if !bytes.HasPrefix(exe, original) || !bytes.HasSuffix(exe, []byte("COMPAS-CONNEXIO-1")) {
		t.Fatal("executable o marcador incorrectes")
	}
	end := len(exe) - len("COMPAS-CONNEXIO-1") - 4
	n := int(binary.LittleEndian.Uint32(exe[end : end+4]))
	data := strings.Split(string(exe[end-n:end]), "\n")
	if len(data) != 3 || data[0] != "https://compas-prova.local:8443/" || data[1] != strings.Repeat("A", 64) {
		t.Fatal("connexio incorrecta")
	}
	der, err := base64.StdEncoding.DecodeString(data[2])
	if err != nil {
		t.Fatal(err)
	}
	block, _ := pem.Decode(root)
	if !bytes.Equal(der, block.Bytes) || bytes.Contains(exe, []byte("PRIVATE KEY")) {
		t.Fatal("només ha d'incorporar el certificat públic")
	}
	if _, err = clientExecutable(original, "compas.local", "pin", []byte("incorrecte")); err == nil {
		t.Fatal("accepta certificat malformat")
	}
}
