// Compàs: Gestió lliure per a societats musicals
// Llançador per a Windows: arranca el servidor integrat de PHP i obri el navegador.
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
using System.Text;
using System.Threading;
using System.Windows.Forms;

[assembly: System.Reflection.AssemblyTitle("Compàs")]
[assembly: System.Reflection.AssemblyDescription("Gestió lliure per a societats musicals")]
[assembly: System.Reflection.AssemblyProduct("Compàs")]
[assembly: System.Reflection.AssemblyCopyright("AGPL-3.0-or-later")]

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
        [STAThread]
        static void Main()
        {
            bool nou;
            using (var mutex = new Mutex(true, "Compas-GestioSocietatsMusicals", out nou))
            {
                var config = Config.Llig();
                if (!nou)
                {
                    // Ja està en marxa: només obrim el navegador
                    Finestra.ObriNavegador("http://localhost:" + config.Port + "/");
                    return;
                }
                Application.EnableVisualStyles();
                Application.SetCompatibleTextRenderingDefault(false);
                Application.Run(new Finestra(config));
            }
        }
    }

    class Config
    {
        public int Port = 8740;
        public bool Navegador = true;
        public string Arrel;

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
                if (clau == "navegador") c.Navegador = !(valor == "0" || valor.ToLowerInvariant() == "no" || valor.ToLowerInvariant() == "false");
            }
            return c;
        }
    }

    class Finestra : Form
    {
        readonly Config config;
        Process php;
        readonly Label estat = new Label();
        readonly LinkLabel enllac = new LinkLabel();
        readonly string token = Guid.NewGuid().ToString("N") + Guid.NewGuid().ToString("N");
        readonly Button obri = new Button();
        StreamWriter registre;
        bool tancant;

        string Url { get { return "http://localhost:" + config.Port + "/"; } }
        string Dades { get { return Path.Combine(config.Arrel, "data"); } }

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
            Controls.Add(new Label { Text = Textos.T("Gestió lliure per a societats musicals", "Gestión libre para sociedades musicales"), Location = new Point(110, 62), AutoSize = true, ForeColor = Color.FromArgb(93, 103, 120) });

            estat.Location = new Point(20, 112);
            estat.Size = new Size(400, 22);
            estat.Text = Textos.T("Arrancant…", "Arrancando…");
            Controls.Add(estat);

            enllac.Location = new Point(20, 136);
            enllac.AutoSize = true;
            enllac.Text = Url;
            enllac.LinkClicked += delegate { ObriNavegador(Url); };
            Controls.Add(enllac);


            obri.Text = Textos.T("Obri Compàs", "Abrir Compàs");
            obri.Location = new Point(20, 212);
            obri.Size = new Size(130, 34);
            obri.Enabled = false;
            obri.Click += delegate { ObriNavegador(Url); };
            Controls.Add(obri);

            var carpeta = new Button { Text = Textos.T("Carpeta de dades", "Carpeta de datos"), Location = new Point(156, 212), Size = new Size(130, 34) };
            carpeta.Click += delegate
            {
                Directory.CreateDirectory(Dades);
                Process.Start("explorer.exe", "\"" + Dades + "\"");
            };
            Controls.Add(carpeta);

            var atura = new Button { Text = Textos.T("Atura i tanca", "Detener y cerrar"), Location = new Point(292, 212), Size = new Size(128, 34) };
            atura.Click += delegate { Close(); };
            Controls.Add(atura);

            Controls.Add(new Label
            {
                Text = Textos.T("Mentre aquesta finestra estiga oberta, Compàs funciona.", "Mientras esta ventana esté abierta, Compàs funciona."),
                Location = new Point(20, 262), AutoSize = true, ForeColor = Color.FromArgb(93, 103, 120), Font = new Font("Segoe UI", 8.5f)
            });

            Shown += delegate { Arranca(); };
            FormClosing += Tancant;
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

            // Només s'escolta en aquest ordinador
            string host = "127.0.0.1";
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
                if (php.HasExited) { esperant.Stop(); return; }
                if (Respon())
                {
                    esperant.Stop();
                    estat.Text = "✔ " + Textos.T("Compàs està en marxa en aquest ordinador:", "Compàs está en marcha en este ordenador:");
                    estat.ForeColor = Color.FromArgb(30, 125, 79);
                    obri.Enabled = true;
                    if (config.Navegador) ObriNavegador(Url);
                }
                else if (intents > 60)
                {
                    esperant.Stop();
                    Error(Textos.T("El servidor no respon. Mira data\\servidor.log.", "El servidor no responde. Mira data\\servidor.log."));
                }
            };
            esperant.Start();
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
                    var r = c.BeginConnect(IPAddress.Loopback, config.Port, null, null);
                    bool ok = r.AsyncWaitHandle.WaitOne(200);
                    if (ok) c.EndConnect(r);
                    return ok && c.Connected;
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
            MessageBox.Show(this, text, "Compàs", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }

        void Tancant(object sender, FormClosingEventArgs e)
        {
            if (php != null && !php.HasExited && e.CloseReason == CloseReason.UserClosing)
            {
                var r = MessageBox.Show(this,
                    Textos.T("Vols tancar Compàs? Si hi ha hagut canvis, abans es farà una còpia de seguretat.",
                             "¿Quieres cerrar Compàs? Si ha habido cambios, antes se hará una copia de seguridad."),
                    "Compàs", MessageBoxButtons.YesNo, MessageBoxIcon.Question);
                if (r != DialogResult.Yes) { e.Cancel = true; return; }
            }
            tancant = true;
            if (php != null && !php.HasExited)
            {
                // Còpia de seguretat en tancar (només si hi ha hagut canvis)
                estat.Text = Textos.T("Guardant la còpia de seguretat…", "Guardando la copia de seguridad…");
                estat.ForeColor = Color.FromArgb(93, 103, 120);
                Cursor = Cursors.WaitCursor;
                Refresh();
                try
                {
                    var peticio = (HttpWebRequest)WebRequest.Create("http://127.0.0.1:" + config.Port + "/?r=copia_tancament&token=" + token);
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
            if (registre != null) { try { registre.Dispose(); } catch { } }
        }

        public static void ObriNavegador(string url)
        {
            try { Process.Start(new ProcessStartInfo(url) { UseShellExecute = true }); } catch { }
        }
    }
}
