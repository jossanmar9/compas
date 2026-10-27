// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import (
	"os/exec"
	"os/user"
	"syscall"
)

// Protegix les claus privades creades per Compàs; no instal·la cap certificat.
func protect(dir string) error {
	u, err := user.Current()
	if err != nil {
		return err
	}
	cmd := exec.Command("icacls.exe", dir, "/inheritance:r", "/grant:r", "*"+u.Uid+":(OI)(CI)F", "*S-1-5-18:(OI)(CI)F")
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	return cmd.Run()
}
