// Confiança inicial del client, explícita i només per a l'usuari actual.
// SPDX-License-Identifier: AGPL-3.0-or-later
using System;
using System.Diagnostics;
using System.IO;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Windows.Forms;

class ConnectaCompas
{
    [STAThread]
    static void Main()
    {
        Application.EnableVisualStyles();
        try {
            string dir = AppDomain.CurrentDomain.BaseDirectory;
            string[] info = File.ReadAllLines(Path.Combine(dir, "connexio.txt"));
            Uri url;
            if (info.Length < 2 || !Uri.TryCreate(info[0], UriKind.Absolute, out url) || url.Scheme != "https"
                || !System.Text.RegularExpressions.Regex.IsMatch(url.Host, @"^[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local$")
                || url.AbsolutePath != "/" || url.UserInfo != "" || url.Query != "" || url.Fragment != "")
                throw new Exception("Adreça local invàlida / Dirección local no válida.");
            var cert = new X509Certificate2(Path.Combine(dir, "compas-ca.crt"));
            string sha;
            using (var h = SHA256.Create()) sha = BitConverter.ToString(h.ComputeHash(cert.RawData)).Replace("-", "");
            if (!string.Equals(sha, info[1], StringComparison.OrdinalIgnoreCase) || cert.NotAfter <= DateTime.Now)
                throw new Exception("Certificat incorrecte o caducat / Certificado incorrecto o caducado.");
            string text = "Compàs · " + url + "\n\nCompara esta empremta amb la del servidor abans de continuar.\nCompara esta huella con la del servidor antes de continuar.\n\nSHA-256:\n" + sha
                + "\n\nVols confiar en el certificat local de l'associació per a este usuari?\n¿Quieres confiar en el certificado local de la asociación para este usuario?"
                + "\n\nNomés si coneixes el servidor i l'empremta coincidix. / Solo si conoces el servidor y la huella coincide.";
            if (MessageBox.Show(text, "Compàs HTTPS", MessageBoxButtons.YesNo, MessageBoxIcon.Question, MessageBoxDefaultButton.Button2) != DialogResult.Yes) return;
            using (var store = new X509Store(StoreName.Root, StoreLocation.CurrentUser)) {
                store.Open(OpenFlags.ReadWrite); store.Add(cert);
            }
            Process.Start(new ProcessStartInfo(url.AbsoluteUri) { UseShellExecute = true });
        } catch (Exception e) { MessageBox.Show(e.Message, "Compàs HTTPS", MessageBoxButtons.OK, MessageBoxIcon.Error); }
    }
}
