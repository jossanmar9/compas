// Proves del llançador sense obrir finestres ni arrancar PHP.
// SPDX-License-Identifier: AGPL-3.0-or-later
using System;
using System.IO;
using System.Net.NetworkInformation;
using Compas;

class LauncherConfigTests
{
    static void Check(bool valid, string message)
    {
        if (!valid) throw new Exception(message);
        Console.WriteLine("PASS: " + message);
    }

    static int Main()
    {
        string ini = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "compas.ini");
        byte[] before = File.Exists(ini) ? File.ReadAllBytes(ini) : null;
        try
        {
            foreach (string ip in new[] { "10.0.0.1", "172.16.0.1", "192.168.1.20" })
                Check(Config.IpPrivada(ip), "IPv4 privada " + ip);
            foreach (string ip in new[] { "0.0.0.0", "8.8.8.8", "127.0.0.1", "192.168.1.999", "::1", "010.0.0.1" })
                Check(!Config.IpPrivada(ip), "rebutja " + ip);
            File.WriteAllText(ini, "lan = 0\nport = 8740\nfinestra = 1\n");
            Config local = Config.Llig();
            Check(!local.Lan && local.Host == "127.0.0.1" && local.Finestra, "mode local per defecte");
            File.WriteAllText(ini, "lan = 1\nnom_lan = compas.local\nfinestra = 1\n");
            Config lan = Config.Llig();
            Check(lan.Lan && lan.Host == "127.0.0.1" && !lan.Finestra && lan.Url == "https://compas.local/", "LAN HTTPS sense IP fixa i PHP només local");
            File.WriteAllText(ini, "lan = 1\nnom_lan = entitat.local\nport_https = 8443\n");
            Check(Config.Llig().Url == "https://entitat.local:8443/", "nom i port HTTPS configurables");
            foreach (string nom in new[] { "compas.evil", "compàs.local", "-compas.local", "compas.local.evil", "../compas.local" }) {
                File.WriteAllText(ini, "lan = 1\nnom_lan = " + nom + "\n");
                bool rejected = false; try { Config.Llig(); } catch (Exception) { rejected = true; }
                Check(rejected, "rebutja nom local invàlid " + nom);
            }
            return 0;
        }
        catch (Exception e) { Console.Error.WriteLine(e); return 1; }
        finally
        {
            if (before == null) File.Delete(ini);
            else File.WriteAllBytes(ini, before);
        }
    }
}
