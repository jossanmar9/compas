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

    [STAThread]
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
            Check(lan.Lan && lan.Host == "127.0.0.1" && lan.Finestra && lan.Url == "https://compas.local/", "LAN HTTPS conserva la finestra pròpia i PHP només local");
            Check(lan.EsAdrecaInterna("https://compas.local/?r=inici"), "visor admet HTTPS del servidor");
            foreach (string url in new[] { "http://compas.local/", "https://altre.local/", "https://compas.local:8443/", "https://usuari@compas.local/" })
                Check(!lan.EsAdrecaInterna(url), "visor rebutja origen diferent " + url);
            string secret = new string('x', 48);
            long ara = 1793090000;
            string ordre = "xarxa|" + ara + "|" + new string('a',32);
            string signatura;
            using (var hmac = new System.Security.Cryptography.HMACSHA256(System.Text.Encoding.UTF8.GetBytes(secret)))
                signatura = BitConverter.ToString(hmac.ComputeHash(System.Text.Encoding.UTF8.GetBytes(ordre))).Replace("-", "").ToLowerInvariant();
            string missatge = ordre + "\n" + signatura + "\n";
            Check(Config.OrdreXarxaValida(missatge,secret,ara), "control local autenticat");
            Check(!Config.OrdreXarxaValida(missatge,secret,ara+31), "rebutja ordre caducada");
            Check(!Config.OrdreXarxaValida(missatge,new string('y',48),ara), "rebutja secret fals");
            Check(!Config.OrdreXarxaValida(missatge.Replace("xarxa|","ordres|"),secret,ara), "rebutja accio arbitraria");
            File.WriteAllText(ini, "lan = 1\nnom_lan = entitat.local\nport_https = 8443\n");
            Check(Config.Llig().Url == "https://entitat.local:8443/", "nom i port HTTPS configurables");
            foreach (string nom in new[] { "compas.evil", "compàs.local", "-compas.local", "compas.local.evil", "../compas.local" }) {
                File.WriteAllText(ini, "lan = 1\nnom_lan = " + nom + "\n");
                bool rejected = false; try { Config.Llig(); } catch (Exception) { rejected = true; }
                Check(rejected, "rebutja nom local invàlid " + nom);
            }
            if (Environment.GetEnvironmentVariable("CI") == "true") {
                using (var servidor = new Finestra(new Config { Lan=true, Finestra=true, Arrel=AppDomain.CurrentDomain.BaseDirectory }))
                using (var visor = new Visor(servidor,8740,"https://compas.local/")) {
                    var flags=System.Reflection.BindingFlags.Instance|System.Reflection.BindingFlags.NonPublic;
                    typeof(Finestra).GetField("visor",flags).SetValue(servidor,visor);
                    var tancament=new System.Windows.Forms.FormClosingEventArgs(System.Windows.Forms.CloseReason.UserClosing,false);
                    typeof(Visor).GetMethod("Tancant",flags).Invoke(visor,new object[]{visor,tancament});
                    Check(!tancament.Cancel && !(bool)typeof(Finestra).GetField("tancant",flags).GetValue(servidor),"tancar el visor LAN no atura el servidor");
                    Check(typeof(Finestra).GetField("visor",flags).GetValue(servidor)==null,"es pot tornar a obrir el visor LAN");
                }
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
