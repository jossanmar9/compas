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
            File.WriteAllText(ini, "lan = 1\nadreca_lan = 8.8.8.8\n");
            bool rejected = false;
            try { Config.Llig(); } catch (Exception) { rejected = true; }
            Check(rejected, "LAN no accepta una adreça pública");
            foreach (NetworkInterface net in NetworkInterface.GetAllNetworkInterfaces())
                foreach (UnicastIPAddressInformation address in net.GetIPProperties().UnicastAddresses)
                {
                    string ip = address.Address.ToString();
                    if (!Config.IpPrivada(ip)) continue;
                    File.WriteAllText(ini, "lan = 1\nadreca_lan = " + ip + "\nfinestra = 1\n");
                    Config lan = Config.Llig();
                    Check(lan.Lan && lan.Host == ip && !lan.Finestra && lan.Url == "http://" + ip + ":8740/", "LAN manté el panell del servidor i usa la IP pròpia");
                    return 0;
                }
            Console.WriteLine("SKIP: no hi ha IPv4 privada local per a la prova LAN positiva");
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
