// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"golang.org/x/sys/windows"
	"os"
	"os/user"
	"path/filepath"
)

// Protegix les claus privades creades per Compàs; no instal·la cap certificat.
func protect(dir string) error {
	u, err := user.Current()
	if err != nil {
		return err
	}
	sd, err := windows.SecurityDescriptorFromString("D:P(A;OICI;FA;;;SY)(A;OICI;FA;;;" + u.Uid + ")")
	if err != nil {
		return err
	}
	dacl, _, err := sd.DACL()
	if err != nil {
		return err
	}
	set := func(path string) error {
		return windows.SetNamedSecurityInfo(path, windows.SE_FILE_OBJECT, windows.DACL_SECURITY_INFORMATION|windows.PROTECTED_DACL_SECURITY_INFORMATION, nil, nil, dacl, nil)
	}
	if err = set(dir); err != nil {
		return err
	}
	for _, name := range []string{"compas-ca.key", "servidor.key", "compas-ca.key.nou", "servidor.key.nou"} {
		path := filepath.Join(dir, name)
		if _, e := os.Stat(path); e == nil {
			if err = set(path); err != nil {
				return err
			}
		} else if !os.IsNotExist(e) {
			return e
		}
	}
	return nil
}
