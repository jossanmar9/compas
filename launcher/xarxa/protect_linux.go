// SPDX-License-Identifier: AGPL-3.0-or-later
package main

import "os"

func protect(dir string) error { return os.Chmod(dir, 0700) }
