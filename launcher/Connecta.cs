// Connexió inicial explícita per a l'usuari actual; el certificat públic viatja amb l'assistent.
// SPDX-License-Identifier: AGPL-3.0-or-later
using System;
using System.Diagnostics;
using System.IO;
using System.Text;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Windows.Forms;
using System.Drawing;
using System.Globalization;

class ConnectaCompas
{
    internal static readonly byte[] Marca = Encoding.ASCII.GetBytes("COMPAS-CONNEXIO-1");
    static bool Es { get { return CultureInfo.CurrentUICulture.TwoLetterISOLanguageName == "es"; } }
    static string T(string ca, string es) { return Es ? es : ca; }

    internal static string[] LligPaquet(string executable)
    {
        using (var f = File.OpenRead(executable)) {
            if (f.Length < Marca.Length + 4) return null;
            f.Seek(-Marca.Length, SeekOrigin.End);
            foreach (byte b in Marca) if (f.ReadByte() != b) return null;
            f.Seek(-Marca.Length - 4, SeekOrigin.End);
            byte[] mida = new byte[4]; if (f.Read(mida,0,4) != 4) throw new InvalidDataException();
            uint n = (uint)(mida[0] | (mida[1] << 8) | (mida[2] << 16) | (mida[3] << 24));
            if (n == 0 || n > 65536 || n > f.Length - Marca.Length - 4) throw new InvalidDataException();
            f.Seek(-Marca.Length - 4 - n, SeekOrigin.End);
            byte[] dades = new byte[(int)n]; int pos = 0;
            while (pos < dades.Length) { int llegits=f.Read(dades,pos,dades.Length-pos); if (llegits==0) throw new EndOfStreamException(); pos+=llegits; }
            string[] info = Encoding.UTF8.GetString(dades).Split('\n');
            if (info.Length != 3) throw new InvalidDataException();
            return info;
        }
    }

    internal static Uri Valida(string adreca, string empremta, X509Certificate2 cert)
    {
        Uri url;
        if (!Uri.TryCreate(adreca, UriKind.Absolute, out url) || url.Scheme != "https"
            || !System.Text.RegularExpressions.Regex.IsMatch(url.Host, @"\A[a-z0-9](?:[a-z0-9-]{0,60}[a-z0-9])?\.local\z")
            || url.AbsolutePath != "/" || url.UserInfo != "" || url.Query != "" || url.Fragment != "")
            throw new Exception(T("L’adreça del servidor no és vàlida.", "La dirección del servidor no es válida."));
        string sha;
        using (var h = SHA256.Create()) sha = BitConverter.ToString(h.ComputeHash(cert.RawData)).Replace("-", "");
        var ca = cert.Extensions["2.5.29.19"] as X509BasicConstraintsExtension;
        if (!string.Equals(sha, empremta, StringComparison.OrdinalIgnoreCase) || cert.NotAfter <= DateTime.Now
            || cert.NotBefore > DateTime.Now || ca == null || !ca.CertificateAuthority)
            throw new Exception(T("El certificat no és correcte o ha caducat. Demana un assistent nou al responsable.", "El certificado no es correcto o ha caducado. Pide un asistente nuevo al responsable."));
        return url;
    }

    [STAThread]
    static void Main()
    {
        Application.EnableVisualStyles();
        try {
            string dir=AppDomain.CurrentDomain.BaseDirectory;
            string[] info=LligPaquet(Application.ExecutablePath);
            X509Certificate2 cert;
            if (info != null) cert=new X509Certificate2(Convert.FromBase64String(info[2]));
            else {
                info=File.ReadAllLines(Path.Combine(dir,"connexio.txt"));
                if(info.Length<2) throw new InvalidDataException();
                cert=new X509Certificate2(Path.Combine(dir,"compas-ca.crt"));
            }
            using(cert) {
                Uri url=Valida(info[0],info[1],cert);
                bool conegut;
                using(var store=new X509Store(StoreName.Root,StoreLocation.CurrentUser)) {
                    store.Open(OpenFlags.ReadOnly);
                    conegut=store.Certificates.Find(X509FindType.FindByThumbprint,cert.Thumbprint,false).Count>0;
                }
                if(!conegut) {
                    using(var f=new Form { Text=T("Connecta amb Compàs","Conecta con Compàs"),ClientSize=new Size(560,360),StartPosition=FormStartPosition.CenterScreen,FormBorderStyle=FormBorderStyle.FixedDialog,MaximizeBox=false,MinimizeBox=false }) {
                        f.Controls.Add(new Label { Text=T("Connecta este ordinador amb l’associació","Conecta este ordenador con la asociación"),Location=new Point(22,20),Size=new Size(516,30),Font=new Font("Segoe UI",13,FontStyle.Bold) });
                        f.Controls.Add(new Label { Text=url.AbsoluteUri,Location=new Point(22,62),Size=new Size(516,28),Font=new Font("Segoe UI",12,FontStyle.Bold) });
                        f.Controls.Add(new Label { Text=T("Comprova amb el responsable que l’empremta és la del servidor. Només hauràs de fer-ho una vegada en este equip.","Comprueba con el responsable que la huella es la del servidor. Solo tendrás que hacerlo una vez en este equipo."),Location=new Point(22,104),Size=new Size(516,48) });
                        f.Controls.Add(new TextBox { Text=info[1].ToUpperInvariant(),ReadOnly=true,Multiline=true,Location=new Point(22,158),Size=new Size(516,44),Font=new Font("Consolas",10) });
                        f.Controls.Add(new Label { Text=T("En continuar, confies en l’autoritat de certificació del servidor per al teu usuari. També pot validar altres serveis signats per ella. Confirma només si coneixes el servidor.","Al continuar, confías en la autoridad de certificación del servidor para tu usuario. También puede validar otros servicios firmados por ella. Confirma solo si conoces el servidor."),Location=new Point(22,217),Size=new Size(516,62) });
                        var no=new Button { Text=T("Cancel·la","Cancelar"),DialogResult=DialogResult.Cancel,Location=new Point(230,302),Size=new Size(100,34) };
                        var si=new Button { Text=T("Confia i obri Compàs","Confiar y abrir Compàs"),DialogResult=DialogResult.OK,Location=new Point(340,302),Size=new Size(198,34) };
                        f.Controls.Add(no); f.Controls.Add(si); f.CancelButton=no; f.AcceptButton=no;
                        if(f.ShowDialog()!=DialogResult.OK) return;
                    }
                    using(var store=new X509Store(StoreName.Root,StoreLocation.CurrentUser)) { store.Open(OpenFlags.ReadWrite); store.Add(cert); }
                }
                // Accés directe segur: URL validada, sense ordres ni dades privades.
                try {
                    string nom="Compas - "+url.Host+".url";
                    File.WriteAllText(Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory),nom),"[InternetShortcut]\r\nURL="+url.AbsoluteUri+"\r\n",Encoding.UTF8);
                } catch { /* El navegador funciona també si l'escriptori no és accessible. */ }
                Process.Start(new ProcessStartInfo(url.AbsoluteUri) { UseShellExecute=true });
            }
        } catch(Exception e) { MessageBox.Show(T("No s’ha pogut connectar. Demana al responsable el fitxer Connecta-Compas.exe del servidor.\n\n","No se ha podido conectar. Pide al responsable el archivo Connecta-Compas.exe del servidor.\n\n")+e.Message,"Compàs",MessageBoxButtons.OK,MessageBoxIcon.Error); }
    }
}
