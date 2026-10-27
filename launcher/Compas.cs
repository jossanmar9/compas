// Compàs: Gestió lliure per a associacions
// Llançador per a Windows: arranca el servidor integrat de PHP i obri Compàs en la seua finestra (Visor.cs,
// amb el component WebView2 de Windows). Si no hi és, prova Microsoft Edge o Google Chrome en mode
// aplicació i, si tampoc, el navegador habitual amb la finestra menuda.
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
//
// Es compila amb el csc.exe de .NET Framework 4 (C# 5), sense dependències externes.

using System;
using System.Diagnostics;
using System.Drawing;
using System.Globalization;
using System.IO;
using System.Net;
using System.Net.NetworkInformation;
using System.Net.Sockets;
using System.Runtime.CompilerServices;
using System.Text;
using System.Threading;
using System.Windows.Forms;
using Microsoft.Win32;

[assembly: System.Reflection.AssemblyTitle("Compàs")]
[assembly: System.Reflection.AssemblyDescription("Gestió lliure per a associacions")]
[assembly: System.Reflection.AssemblyProduct("Compàs")]
[assembly: System.Reflection.AssemblyCopyright("© 2026 José Vicente Sanjuan · AGPL-3.0-or-later")]

namespace Compas
{
    static class Textos
    {
        static readonly bool Es = CultureInfo.CurrentUICulture.TwoLetterISOLanguageName == "es"
            && !CultureInfo.CurrentUICulture.Name.StartsWith("ca", StringComparison.OrdinalIgnoreCase);

        public static string T(string ca, string es) { return Es ? es : ca; }
    }

    static class Program
    {
        public static bool Reinicia;
        [STAThread]
        static void Main(string[] args)
        {
            if (args.Length == 2 && args[0] == "--tallafoc") {
                int port;
                if (!int.TryParse(args[1], out port) || port < 1 || port > 65535) { Environment.ExitCode = 1; return; }
                string helper = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "compas-xarxa.exe");
                foreach (string regla in new[] { "name=\"Compas HTTPS\" protocol=TCP localport=" + port, "name=\"Compas descobriment local\" protocol=UDP localport=5353" }) {
                    using (var p = Process.Start(new ProcessStartInfo("netsh.exe", "advfirewall firewall add rule " + regla + " dir=in action=allow profile=private remoteip=localsubnet program=\"" + helper + "\"") { UseShellExecute = false, CreateNoWindow = true })) {
                        p.WaitForExit(); if (p.ExitCode != 0) { Environment.ExitCode = 1; return; }
                    }
                }
                return;
            }
            bool nou;
            using (var mutex = new Mutex(true, "Compas-GestioSocietatsMusicals", out nou))
            {
                Config config;
                try { config = Config.Llig(); }
                catch (Exception ex) { MessageBox.Show(ex.Message, "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Error); return; }
                if (!nou)
                {
                    // Ja està en marxa: es demana a la instància oberta que mostre la seua finestra
                    try
                    {
                        using (var avis = EventWaitHandle.OpenExisting(Finestra.NomAvis)) avis.Set();
                        return;
                    }
                    catch { }
                    string url = config.Url;
                    if (!config.Finestra || AppNavegador.Obri(url) == null) Finestra.ObriNavegador(url);
                    return;
                }
                Application.EnableVisualStyles();
                Application.SetCompatibleTextRenderingDefault(false);
                Application.Run(new Finestra(config));
            }
            if (Reinicia) Process.Start(new ProcessStartInfo(Application.ExecutablePath) { UseShellExecute = true });
        }
    }

    class Config
    {
        public int Port = 8740;
        public bool Navegador = true;
        public bool Finestra = true;
        public string Arrel;
        public bool Lan = false;
        public string NomLan = "compas.local";
        public int PortHttps = 443;
        public string Host { get { return "127.0.0.1"; } }
        public string Url { get { return Lan ? "https://" + NomLan + (PortHttps == 443 ? "" : ":" + PortHttps) + "/" : "http://localhost:" + Port + "/"; } }
        public string UrlInterna { get { return "http://127.0.0.1:" + Port + "/"; } }

        public static bool IpPrivada(string text)
        {
            IPAddress ip;
            if (!IPAddress.TryParse(text, out ip) || ip.AddressFamily != AddressFamily.InterNetwork || ip.ToString() != text) return false;
            byte[] b = ip.GetAddressBytes();
            return b[0] == 10 || (b[0] == 172 && b[1] >= 16 && b[1] <= 31) || (b[0] == 192 && b[1] == 168);
        }

        static bool Si(string v)
        {
            v = v.ToLowerInvariant();
            return !(v == "0" || v == "no" || v == "false");
        }

        public static Config Llig()
        {
            var c = new Config();
            c.Arrel = AppDomain.CurrentDomain.BaseDirectory.TrimEnd('\\');
            string fitxer = Path.Combine(c.Arrel, "compas.ini");
            if (!File.Exists(fitxer)) return c;
            foreach (string linia in File.ReadAllLines(fitxer))
            {
                string l = linia.Trim();
                if (l.Length == 0 || l.StartsWith(";") || l.StartsWith("#") || !l.Contains("=")) continue;
                string clau = l.Substring(0, l.IndexOf('=')).Trim().ToLowerInvariant();
                string valor = l.Substring(l.IndexOf('=') + 1).Trim();
                int n;
                if (clau == "port" && int.TryParse(valor, out n) && n > 1023 && n < 65536) c.Port = n;
                if (clau == "navegador") c.Navegador = Si(valor);
                if (clau == "finestra") c.Finestra = Si(valor);
                if (clau == "lan") c.Lan = Si(valor);
                if (clau == "nom_lan") c.NomLan = valor.ToLowerInvariant();
                if (clau == "port_https" && int.TryParse(valor, out n) && n > 0 && n < 65536) c.PortHttps = n;
            }
            if (c.Lan)
            {
                if (!System.Text.RegularExpressions.Regex.IsMatch(c.NomLan, @"^[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local$"))
                    throw new Exception(Textos.T("Nom local invàlid. Exemple: compas.local", "Nombre local no válido. Ejemplo: compas.local"));
                c.Finestra = false; // El panell del servidor queda obert encara que es tanque el navegador.
            }
            return c;
        }
    }

    /// <summary>
    /// Finestra pròpia de Compàs: Microsoft Edge (o Google Chrome) en mode aplicació, sense pestanyes
    /// ni barra d'adreces, amb un perfil separat del navegador de l'usuari.
    /// </summary>
    static class AppNavegador
    {
        static string Perfil
        {
            get { return Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "Compas", "finestra"); }
        }

        static string Troba()
        {
            string pf86 = Environment.GetFolderPath(Environment.SpecialFolder.ProgramFilesX86);
            string pf = Environment.GetEnvironmentVariable("ProgramW6432");
            if (string.IsNullOrEmpty(pf)) pf = Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles);
            string local = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
            string[] candidats =
            {
                RutaRegistrada("msedge.exe"),
                Path.Combine(pf86, @"Microsoft\Edge\Application\msedge.exe"),
                Path.Combine(pf, @"Microsoft\Edge\Application\msedge.exe"),
                Path.Combine(local, @"Microsoft\Edge\Application\msedge.exe"),
                RutaRegistrada("chrome.exe"),
                Path.Combine(pf, @"Google\Chrome\Application\chrome.exe"),
                Path.Combine(pf86, @"Google\Chrome\Application\chrome.exe"),
                Path.Combine(local, @"Google\Chrome\Application\chrome.exe")
            };
            foreach (string c in candidats)
            {
                try { if (!string.IsNullOrEmpty(c) && File.Exists(c)) return c; } catch { }
            }
            return null;
        }

        static string RutaRegistrada(string exe)
        {
            foreach (RegistryKey arrel in new[] { Registry.LocalMachine, Registry.CurrentUser })
            {
                try
                {
                    using (RegistryKey k = arrel.OpenSubKey(@"SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\" + exe))
                    {
                        if (k == null) continue;
                        string v = k.GetValue(null) as string;
                        if (!string.IsNullOrEmpty(v)) return v.Trim('"');
                    }
                }
                catch { }
            }
            return null;
        }

        /// <summary>Obri una finestra de Compàs i en retorna el procés, o null si no hi ha Edge ni Chrome</summary>
        public static Process Obri(string url)
        {
            string exe = Troba();
            if (exe == null) return null;
            try
            {
                Directory.CreateDirectory(Perfil);
                string args = "--app=\"" + url + "\" --user-data-dir=\"" + Perfil + "\""
                    + " --no-first-run --no-default-browser-check --disable-background-mode --disable-sync"
                    + " --disable-features=msEdgeStartupBoost,msImplicitSignin,msSmartScreenProtection,Translate --window-size=1280,860";
                return Process.Start(new ProcessStartInfo(exe, args) { UseShellExecute = false });
            }
            catch { return null; }
        }
    }

    class Finestra : Form
    {
        readonly Config config;
        Process php;
        Process https;
        readonly Label estat = new Label();
        readonly LinkLabel enllac = new LinkLabel();
        readonly string token = Guid.NewGuid().ToString("N") + Guid.NewGuid().ToString("N");
        readonly Button obri = new Button();
        StreamWriter registre;
        bool tancant;
        bool visible;              // la finestra menuda només es mostra si cal (error, o sense Edge ni Chrome)
        bool arrancat;
        bool tancaSensePreguntar;  // l'usuari ha tancat la finestra de Compàs: no cal confirmar
        Process app;
        DateTime appObertA;
        NotifyIcon safata;
        Form visor;                // la finestra de Compàs (Visor), si el component WebView2 hi és
        EventWaitHandle avisObri;  // una segona execució de Compas.exe demana que es mostre la finestra

        public const string NomAvis = @"Local\Compas-ObriFinestra";

        string Url { get { return config.Url; } }
        string Dades
        {
            get
            {
                string d = Environment.GetEnvironmentVariable("COMPAS_DADES");
                return string.IsNullOrEmpty(d) ? Path.Combine(config.Arrel, "data") : d;
            }
        }

        public Finestra(Config c)
        {
            config = c;
            Text = "Compàs";
            try { Icon = Icon.ExtractAssociatedIcon(Application.ExecutablePath); } catch { }
            FormBorderStyle = FormBorderStyle.FixedSingle;
            MaximizeBox = false;
            StartPosition = FormStartPosition.CenterScreen;
            ClientSize = new Size(440, 300);
            BackColor = Color.White;
            Font = new Font("Segoe UI", 9.5f);

            var logo = new PictureBox { Location = new Point(20, 20), Size = new Size(72, 72), SizeMode = PictureBoxSizeMode.Zoom };
            try
            {
                using (var s = typeof(Finestra).Assembly.GetManifestResourceStream("icona.png"))
                    if (s != null) logo.Image = Image.FromStream(s);
            }
            catch { }
            Controls.Add(logo);

            Controls.Add(new Label { Text = "Compàs", Location = new Point(108, 22), AutoSize = true, Font = new Font("Segoe UI", 18f, FontStyle.Bold), ForeColor = Color.FromArgb(20, 40, 75) });
            Controls.Add(new Label { Text = Textos.T("Gestió lliure per a associacions", "Gestión libre para asociaciones"), Location = new Point(110, 62), AutoSize = true, ForeColor = Color.FromArgb(93, 103, 120) });

            estat.Location = new Point(20, 112);
            estat.Size = new Size(400, 22);
            estat.Text = Textos.T("Arrancant…", "Arrancando…");
            Controls.Add(estat);

            enllac.Location = new Point(20, 136);
            enllac.AutoSize = true;
            enllac.Text = Url;
            enllac.LinkClicked += delegate { ObriNavegador(Url); };
            Controls.Add(enllac);

            var xarxa = new Button { Text = Textos.T("Xarxa local / HTTPS…", "Red local / HTTPS…"), Location = new Point(20, 170), Size = new Size(195, 30) };
            xarxa.Click += delegate { ConfiguraXarxa(); }; Controls.Add(xarxa);
            var clients = new Button { Text = Textos.T("Preparar altres equips", "Preparar otros equipos"), Location = new Point(225, 170), Size = new Size(195, 30) };
            clients.Click += delegate {
                string dir = Path.Combine(Dades, "https");
                if (!File.Exists(Path.Combine(dir, "Client-Compas.zip"))) { MessageBox.Show(this, Textos.T("Activa primer la xarxa local.", "Activa primero la red local."), "Compàs"); return; }
                MessageBox.Show(this, Textos.T("Passa Client-Compas.zip als altres equips (per exemple, amb USB). Cal confirmar l'empremta del servidor una sola vegada; després només han d'obrir la URL.\n\n", "Pasa Client-Compas.zip a los demás equipos (por ejemplo, con USB). Hay que confirmar la huella del servidor una sola vez; después solo tienen que abrir la URL.\n\n") + File.ReadAllText(Path.Combine(dir, "empremta.txt")), "Compàs HTTPS");
                Process.Start(new ProcessStartInfo("explorer.exe", "/select,\"" + Path.Combine(dir, "Client-Compas.zip") + "\"") { UseShellExecute = true });
            }; Controls.Add(clients);


            obri.Text = Textos.T("Obri Compàs", "Abrir Compàs");
            obri.Location = new Point(20, 212);
            obri.Size = new Size(130, 34);
            obri.Enabled = false;
            obri.Click += delegate { ObriFinestra(); };
            Controls.Add(obri);

            var carpeta = new Button { Text = Textos.T("Carpeta de dades", "Carpeta de datos"), Location = new Point(156, 212), Size = new Size(130, 34) };
            carpeta.Click += delegate { ObriCarpeta(); };
            Controls.Add(carpeta);

            var atura = new Button { Text = Textos.T("Atura i tanca", "Detener y cerrar"), Location = new Point(292, 212), Size = new Size(128, 34) };
            atura.Click += delegate { Close(); };
            Controls.Add(atura);

            Controls.Add(new Label
            {
                Text = Textos.T("Mentre aquesta finestra estiga oberta, Compàs funciona.", "Mientras esta ventana esté abierta, Compàs funciona."),
                Location = new Point(20, 262), AutoSize = true, ForeColor = Color.FromArgb(93, 103, 120), Font = new Font("Segoe UI", 8.5f)
            });

            visible = !config.Finestra;
            FormClosing += Tancant;
            EscoltaAvisos();
        }

        void EscoltaAvisos()
        {
            try { avisObri = new EventWaitHandle(false, EventResetMode.AutoReset, NomAvis); }
            catch { return; }
            var fil = new Thread(delegate ()
            {
                while (true)
                {
                    try
                    {
                        avisObri.WaitOne();
                        if (tancant || IsDisposed) return;
                        BeginInvoke((MethodInvoker)ObriFinestra);
                    }
                    catch { return; }
                }
            });
            fil.IsBackground = true;
            fil.Start();
        }

        void Log(string text)
        {
            if (registre == null) return;
            lock (registre) { try { registre.WriteLine("[Compàs] " + text); } catch { } }
        }

        // Mentre no cal, la finestra menuda queda amagada (però el programa continua en marxa)
        protected override void SetVisibleCore(bool value)
        {
            if (!IsHandleCreated) CreateHandle();
            if (!arrancat)
            {
                arrancat = true;
                BeginInvoke((MethodInvoker)Arranca);
            }
            base.SetVisibleCore(visible && value);
        }

        void Mostra()
        {
            if (visible) return;
            visible = true;
            Show();
            Activate();
        }

        void CreaSafata()
        {
            if (safata != null) return;
            var menu = new ContextMenuStrip();
            menu.Items.Add(Textos.T("Obri Compàs", "Abrir Compàs"), null, delegate { ObriFinestra(); });
            menu.Items.Add(Textos.T("Carpeta de dades", "Carpeta de datos"), null, delegate { ObriCarpeta(); });
            menu.Items.Add(Textos.T("Xarxa local / HTTPS…", "Red local / HTTPS…"), null, delegate { Mostra(); ConfiguraXarxa(); });
            menu.Items.Add(new ToolStripSeparator());
            menu.Items.Add(Textos.T("Atura i tanca", "Detener y cerrar"), null, delegate { Close(); });
            safata = new NotifyIcon
            {
                Text = "Compàs",
                Icon = this.Icon ?? SystemIcons.Application,
                ContextMenuStrip = menu,
                Visible = true
            };
            safata.DoubleClick += delegate { ObriFinestra(); };
        }

        public void ObriCarpeta()
        {
            try
            {
                Directory.CreateDirectory(Dades);
                Process.Start("explorer.exe", "\"" + Dades + "\"");
            }
            catch { }
        }

        void ObriFinestra()
        {
            if (visor != null && !visor.IsDisposed) { PortaVisor(); return; }
            if (!config.Finestra || AppNavegador.Obri(Url) == null) ObriNavegador(Url);
        }

        // Els tipus de WebView2 només es carreguen ací: si falten les biblioteques, el llançador continua igual
        [MethodImpl(MethodImplOptions.NoInlining)]
        static bool VisorDisponible() { return Visor.Disponible(); }

        [MethodImpl(MethodImplOptions.NoInlining)]
        Form CreaVisor() { return new Visor(this, config.Port, Url); }

        [MethodImpl(MethodImplOptions.NoInlining)]
        void PortaVisor() { ((Visor)visor).Porta(); }

        /// <summary>Obri la finestra de Compàs; quan l'usuari la tanca, Compàs fa la còpia i s'atura</summary>
        void ObriFinestraPropia()
        {
            bool ok = false;
            try { ok = VisorDisponible(); } catch (Exception ex) { Log("Visor: " + ex.Message); }
            if (ok)
            {
                try
                {
                    visor = CreaVisor();
                    visor.Show();
                    CreaSafata();
                    return;
                }
                catch (Exception ex) { Log("Visor: " + ex.Message); visor = null; }
            }
            ObriAppNavegador();
        }

        /// <summary>El visor no s'ha pogut iniciar: es prova Edge o Chrome en mode aplicació</summary>
        public void VisorHaFallat(string motiu)
        {
            Log("Visor: " + motiu);
            if (visor != null) { try { visor.Dispose(); } catch { } visor = null; }
            ObriAppNavegador();
        }

        /// <summary>L'usuari ha tancat la finestra de Compàs: còpia de seguretat i fi</summary>
        public void TancaDesDelVisor()
        {
            tancaSensePreguntar = true;
            Close();
        }

        void ObriAppNavegador()
        {
            app = AppNavegador.Obri(Url);
            if (app != null) CreaSafata();
            if (app == null)
            {
                // Sense Edge ni Chrome: navegador habitual i finestra menuda, com abans
                Mostra();
                if (config.Navegador) ObriNavegador(Url);
                return;
            }
            appObertA = DateTime.Now;
            app.EnableRaisingEvents = true;
            app.Exited += delegate
            {
                if (tancant) return;
                try
                {
                    BeginInvoke((MethodInvoker)delegate
                    {
                        if (tancant) return;
                        // Si s'acaba de seguida, la finestra l'ha agafada un altre procés del mateix perfil i no
                        // sabrem quan es tanca: es mostra la finestra menuda perquè l'usuari puga aturar Compàs.
                        if ((DateTime.Now - appObertA).TotalSeconds < 5) { Mostra(); return; }
                        tancaSensePreguntar = true;
                        Close();
                    });
                }
                catch { }
            };
        }

        void Arranca()
        {
            string phpExe = Path.Combine(config.Arrel, "php", "php.exe");
            string app = Path.Combine(config.Arrel, "app");
            if (!File.Exists(phpExe) || !File.Exists(Path.Combine(app, "index.php")))
            {
                Error(Textos.T("Falten fitxers del programa (php o app). Torna a descomprimir o instal·lar Compàs.",
                               "Faltan archivos del programa (php o app). Vuelve a descomprimir o instalar Compàs."));
                return;
            }
            if (!PortLliure(config.Port))
            {
                Error(string.Format(Textos.T("El port {0} ja està en ús per un altre programa. Canvia'l a compas.ini.",
                                             "El puerto {0} ya está en uso por otro programa. Cámbialo en compas.ini."), config.Port));
                return;
            }
            Directory.CreateDirectory(Dades);
            try
            {
                registre = new StreamWriter(Path.Combine(Dades, "servidor.log"), RotaRegistre(), Encoding.UTF8) { AutoFlush = true };
            }
            catch { registre = null; }

            // PHP queda sempre en loopback; el servidor HTTPS és l’únic punt d’entrada LAN.
            string host = config.Host;
            string phpDir = Path.Combine(config.Arrel, "php");
            var args = new StringBuilder();
            args.Append("-c \"").Append(Path.Combine(phpDir, "php.ini")).Append("\" ");
            args.Append("-d extension_dir=\"").Append(Path.Combine(phpDir, "ext")).Append("\" ");
            args.Append("-d error_log=\"").Append(Path.Combine(Dades, "php-errors.log")).Append("\" ");
            args.Append("-S ").Append(host).Append(':').Append(config.Port).Append(' ');
            args.Append("-t \"").Append(app).Append("\" \"").Append(Path.Combine(app, "index.php")).Append('"');

            var psi = new ProcessStartInfo(phpExe, args.ToString())
            {
                UseShellExecute = false,
                CreateNoWindow = true,
                RedirectStandardError = true,
                RedirectStandardOutput = true,
                WorkingDirectory = config.Arrel
            };
            psi.EnvironmentVariables["COMPAS_DADES"] = Dades;
            psi.EnvironmentVariables["COMPAS_TOKEN"] = token;
            psi.EnvironmentVariables["COMPAS_LAN"] = config.Lan ? "1" : "0";
            psi.EnvironmentVariables["COMPAS_LAN_IP"] = "";
            psi.EnvironmentVariables["COMPAS_PROXY_TOKEN"] = "";
            psi.EnvironmentVariables["COMPAS_NOM_LAN"] = "";
            if (config.Lan) {
                psi.EnvironmentVariables["COMPAS_PROXY_TOKEN"] = token + "-proxy";
                psi.EnvironmentVariables["COMPAS_NOM_LAN"] = config.NomLan;
            }
            php = new Process { StartInfo = psi, EnableRaisingEvents = true };
            php.ErrorDataReceived += Registra;
            php.OutputDataReceived += Registra;
            php.Exited += delegate
            {
                if (tancant) return;
                BeginInvoke((MethodInvoker)delegate
                {
                    Error(Textos.T("El servidor s'ha aturat. Mira data\\servidor.log i torna a obrir Compàs.",
                                   "El servidor se ha detenido. Mira data\\servidor.log y vuelve a abrir Compàs."));
                });
            };
            try
            {
                php.Start();
                php.BeginErrorReadLine();
                php.BeginOutputReadLine();
                if (config.Lan) ArrancaHttps();
            }
            catch (Exception ex)
            {
                Error(ex.Message);
                return;
            }

            var esperant = new System.Windows.Forms.Timer { Interval = 250 };
            int intents = 0;
            esperant.Tick += delegate
            {
                intents++;
                if (php.HasExited || (config.Lan && (https == null || https.HasExited))) { esperant.Stop(); return; }
                if (Respon())
                {
                    esperant.Stop();
                    estat.Text = "✔ " + (config.Lan ? Textos.T("Servidor LAN en marxa. Comparteix esta adreça:", "Servidor LAN en marcha. Comparte esta dirección:") : Textos.T("Compàs està en marxa en aquest ordinador:", "Compàs está en marcha en este ordenador:"));
                    estat.ForeColor = Color.FromArgb(30, 125, 79);
                    obri.Enabled = true;
                    if (config.Finestra) ObriFinestraPropia();
                    else if (config.Navegador) ObriNavegador(Url);
                }
                else if (intents > 60)
                {
                    esperant.Stop();
                    Error(Textos.T("El servidor no respon. Mira data\\servidor.log.", "El servidor no responde. Mira data\\servidor.log."));
                }
            };
            esperant.Start();
        }

        void ConfiguraXarxa()
        {
            using (var f = new Form { Text = "Compàs · HTTPS", ClientSize = new Size(470, 225), StartPosition = FormStartPosition.CenterParent, FormBorderStyle = FormBorderStyle.FixedDialog, MaximizeBox = false, MinimizeBox = false }) {
                var activa = new CheckBox { Text = Textos.T("Compartir Compàs en la xarxa privada", "Compartir Compàs en la red privada"), Checked = config.Lan, Location = new Point(20, 18), AutoSize = true };
                var nom = new TextBox { Text = config.NomLan, Location = new Point(20, 75), Width = 420 };
                f.Controls.Add(activa); f.Controls.Add(nom);
                f.Controls.Add(new Label { Text = Textos.T("Adreça estable, sense IP fixa:", "Dirección estable, sin IP fija:"), Location = new Point(20, 50), AutoSize = true });
                f.Controls.Add(new Label { Text = Textos.T("HTTPS amb certificat local. Només xarxes privades.\nCada equip confirma el certificat una vegada.", "HTTPS con certificado local. Solo redes privadas.\nCada equipo confirma el certificado una vez."), Location = new Point(20, 108), Size = new Size(430, 48) });
                var desa = new Button { Text = Textos.T("Desa i reinicia", "Guardar y reiniciar"), Location = new Point(270, 175), Size = new Size(175, 32) };
                f.Controls.Add(desa);
                desa.Click += delegate {
                    string n = nom.Text.Trim().ToLowerInvariant();
                    if (!System.Text.RegularExpressions.Regex.IsMatch(n, @"^[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local$")) { MessageBox.Show(f, "Exemple: compas.local"); return; }
                    try {
                        if (activa.Checked) {
                            if (MessageBox.Show(f, Textos.T("Vols permetre HTTPS i descobriment local de Compàs en el tallafoc, només per a la xarxa privada? Windows demanarà permisos d'administrador. No obri accés des d'Internet.", "¿Quieres permitir HTTPS y descubrimiento local de Compàs en el cortafuegos, solo para la red privada? Windows pedirá permisos de administrador. No abre acceso desde Internet."), "Compàs", MessageBoxButtons.YesNo, MessageBoxIcon.Question, MessageBoxDefaultButton.Button2) != DialogResult.Yes) return;
                            using (var p = Process.Start(new ProcessStartInfo(Application.ExecutablePath, "--tallafoc " + config.PortHttps) { UseShellExecute = true, Verb = "runas", WindowStyle = ProcessWindowStyle.Hidden })) {
                                p.WaitForExit(); if (p.ExitCode != 0) throw new Exception(Textos.T("No s'ha pogut preparar el tallafoc.", "No se ha podido preparar el cortafuegos."));
                            }
                        }
                        File.AppendAllText(Path.Combine(config.Arrel, "compas.ini"), "\r\nlan = " + (activa.Checked ? "1" : "0") + "\r\nnom_lan = " + n + "\r\n", Encoding.UTF8);
                        Program.Reinicia = true; tancaSensePreguntar = true; f.Close(); Close();
                    } catch (Exception e) { MessageBox.Show(f, e.Message, "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Error); }
                };
                f.ShowDialog(this);
            }
        }

        void ArrancaHttps()
        {
            string exe = Path.Combine(config.Arrel, "compas-xarxa.exe");
            string dir = Path.Combine(Dades, "https");
            string args = "-dades \"" + dir + "\" -nom " + config.NomLan + " -port " + config.PortHttps;
            if (!File.Exists(exe)) throw new Exception(Textos.T("Falta el servidor HTTPS. Reinstal·la Compàs.", "Falta el servidor HTTPS. Reinstala Compàs."));
            using (var prepara = Process.Start(new ProcessStartInfo(exe, args + " -prepara") { UseShellExecute = false, CreateNoWindow = true })) {
                if (!prepara.WaitForExit(15000)) { prepara.Kill(); throw new Exception("No es pot preparar HTTPS."); }
                if (prepara.ExitCode != 0) throw new Exception(Textos.T("No es poden preparar els certificats HTTPS.", "No se pueden preparar los certificados HTTPS."));
            }
            var cert = new System.Security.Cryptography.X509Certificates.X509Certificate2(Path.Combine(dir, "compas-ca.crt"));
            using (var store = new System.Security.Cryptography.X509Certificates.X509Store("Root", System.Security.Cryptography.X509Certificates.StoreLocation.CurrentUser)) {
                store.Open(System.Security.Cryptography.X509Certificates.OpenFlags.ReadWrite);
                if (store.Certificates.Find(System.Security.Cryptography.X509Certificates.X509FindType.FindByThumbprint, cert.Thumbprint, false).Count == 0) {
                    string pin = File.ReadAllText(Path.Combine(dir, "empremta.txt"));
                    if (MessageBox.Show(this, Textos.T("Vols confiar en el certificat HTTPS d'este servidor per al teu usuari?\nEls altres equips han de confirmar la mateixa empremta una vegada.\n\n", "¿Quieres confiar en el certificado HTTPS de este servidor para tu usuario?\nLos demás equipos deben confirmar la misma huella una vez.\n\n") + pin, "Compàs HTTPS", MessageBoxButtons.YesNo, MessageBoxIcon.Question, MessageBoxDefaultButton.Button2) != DialogResult.Yes)
                        throw new Exception(Textos.T("No s'ha instal·lat el certificat. Pots tornar-ho a provar quan vulgues.", "No se ha instalado el certificado. Puedes volver a intentarlo cuando quieras."));
                    store.Add(cert);
                }
            }
            var psi = new ProcessStartInfo(exe, args + " -backend 127.0.0.1:" + config.Port) { UseShellExecute = false, CreateNoWindow = true, RedirectStandardError = true, RedirectStandardOutput = true, WorkingDirectory = config.Arrel };
            psi.EnvironmentVariables["COMPAS_PROXY_TOKEN"] = token + "-proxy";
            https = new Process { StartInfo = psi, EnableRaisingEvents = true };
            https.ErrorDataReceived += Registra; https.OutputDataReceived += Registra;
            https.Exited += delegate { if (!tancant) BeginInvoke((MethodInvoker)delegate { Error(Textos.T("El servidor HTTPS s'ha aturat. Revisa servidor.log (port, nom local o tallafoc).", "El servidor HTTPS se ha detenido. Revisa servidor.log (puerto, nombre local o cortafuegos).")); }); };
            https.Start(); https.BeginErrorReadLine(); https.BeginOutputReadLine();
        }

        bool RotaRegistre()
        {
            // Si el registre passa de 2 MB, comencem de nou
            string f = Path.Combine(Dades, "servidor.log");
            return !(File.Exists(f) && new FileInfo(f).Length > 2 * 1024 * 1024);
        }

        void Registra(object sender, DataReceivedEventArgs e)
        {
            if (e.Data == null || registre == null) return;
            lock (registre)
            {
                try { registre.WriteLine(e.Data); } catch { }
            }
        }

        bool Respon()
        {
            try
            {
                using (var c = new TcpClient())
                {
                    var r = c.BeginConnect(IPAddress.Parse(config.Host), config.Port, null, null);
                    bool ok = r.AsyncWaitHandle.WaitOne(200);
                    if (ok) c.EndConnect(r);
                    if (!ok || !c.Connected) return false;
                    if (config.Lan) {
                        using (var s = new TcpClient()) {
                            var h = s.BeginConnect(IPAddress.Loopback, config.PortHttps, null, null);
                            if (!h.AsyncWaitHandle.WaitOne(200)) return false;
                            s.EndConnect(h); return s.Connected;
                        }
                    }
                    return true;
                }
            }
            catch { return false; }
        }

        static bool PortLliure(int port)
        {
            foreach (var ep in IPGlobalProperties.GetIPGlobalProperties().GetActiveTcpListeners())
                if (ep.Port == port) return false;
            return true;
        }

        void Error(string text)
        {
            estat.Text = "✖ " + Textos.T("No s'ha pogut arrancar", "No se ha podido arrancar");
            estat.ForeColor = Color.FromArgb(179, 38, 30);
            obri.Enabled = false;
            Mostra();
            MessageBox.Show(this, text, "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }

        void Tancant(object sender, FormClosingEventArgs e)
        {
            if (php != null && !php.HasExited && e.CloseReason == CloseReason.UserClosing && !tancaSensePreguntar)
            {
                var r = MessageBox.Show(this,
                    Textos.T(config.Lan ? "Vols aturar el servidor per a tots els usuaris? Abans es farà una còpia de seguretat." : "Vols tancar Compàs? Si hi ha hagut canvis, abans es farà una còpia de seguretat.",
                             config.Lan ? "¿Quieres detener el servidor para todos los usuarios? Antes se hará una copia de seguridad." : "¿Quieres cerrar Compàs? Si ha habido cambios, antes se hará una copia de seguridad."),
                    "Compàs", MessageBoxButtons.YesNo, MessageBoxIcon.Question);
                if (r != DialogResult.Yes) { e.Cancel = true; return; }
            }
            tancant = true;
            // Si es tanca des de la finestra menuda o la safata, també es tanca la finestra de Compàs
            try { if (app != null && !app.HasExited) app.CloseMainWindow(); } catch { }
            try { if (avisObri != null) avisObri.Set(); } catch { }
            if (safata != null) safata.Text = Textos.T("Compàs: guardant la còpia…", "Compàs: guardando la copia…");
            if (php != null && !php.HasExited)
            {
                // Còpia de seguretat en tancar (només si hi ha hagut canvis)
                estat.Text = Textos.T("Guardant la còpia de seguretat…", "Guardando la copia de seguridad…");
                estat.ForeColor = Color.FromArgb(93, 103, 120);
                Cursor = Cursors.WaitCursor;
                Refresh();
                try
                {
                    var peticio = (HttpWebRequest)WebRequest.Create(config.UrlInterna + "?r=copia_tancament");
                    peticio.Headers["X-Compas-Token"] = token;
                    if (config.Lan) {
                        peticio.Host = config.NomLan;
                        peticio.Headers["X-Compas-Proxy-Token"] = token + "-proxy";
                        peticio.Headers["X-Compas-Client"] = "127.0.0.1";
                        peticio.Headers["X-Compas-Local"] = "1";
                    }
                    peticio.Proxy = null;
                    peticio.Timeout = 60000;
                    using (var resposta = (HttpWebResponse)peticio.GetResponse())
                    using (var lector = new StreamReader(resposta.GetResponseStream()))
                    {
                        string r = lector.ReadToEnd();
                        if (r == "ok-externa-error")
                            MessageBox.Show(this, Textos.T("S'ha fet la còpia, però no s'ha pogut guardar a la carpeta externa. Comprova que el disc està connectat.",
                                                           "Se ha hecho la copia, pero no se ha podido guardar en la carpeta externa. Comprueba que el disco está conectado."),
                                            "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
                catch (Exception ex)
                {
                    MessageBox.Show(this, Textos.T("No s'ha pogut fer la còpia en tancar: ", "No se ha podido hacer la copia al cerrar: ") + ex.Message,
                                    "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            }
            try { if (php != null && !php.HasExited) { php.Kill(); php.WaitForExit(3000); } } catch { }
            try { if (https != null && !https.HasExited) { https.Kill(); https.WaitForExit(3000); } } catch { }
            if (registre != null) { try { registre.Dispose(); } catch { } }
            if (safata != null) { safata.Visible = false; safata.Dispose(); }
        }

        public static void ObriNavegador(string url)
        {
            try { Process.Start(new ProcessStartInfo(url) { UseShellExecute = true }); } catch { }
        }
    }
}
