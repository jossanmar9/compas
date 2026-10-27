// Proves del paquet Windows sense instal·lar certificats ni obrir finestres.
// SPDX-License-Identifier: AGPL-3.0-or-later
using System;
using System.IO;
using System.Text;
using System.Security.Cryptography.X509Certificates;
class ConnectaTests {
 static void Check(bool ok,string text) { if(!ok) throw new Exception(text);Console.WriteLine("PASS: "+text); }
 static int Main(string[] args) {
  try {
   string file=Path.Combine(args[0],"Connecta-Compas.exe");
   string[] data=ConnectaCompas.LligPaquet(file);
   Check(data!=null && data.Length==3,"executable amb connexio incorporada");
   using(var cert=new X509Certificate2(Convert.FromBase64String(data[2]))) {
    Check(ConnectaCompas.Valida(data[0],data[1],cert).Host=="compas-prova.local","adreca i certificat de la instal·lacio correctes");
    foreach(string url in new[]{"http://compas-prova.local/","https://extern.example/","https://compas-prova.local/?x=1","https://usuari@compas-prova.local/","https://compas-prova.local/fitxer"}) {
     bool rebutjat=false;try{ConnectaCompas.Valida(url,data[1],cert);}catch{rebutjat=true;}Check(rebutjat,"rebutja origen o ruta insegurs");
    }
    bool fals=false;try{ConnectaCompas.Valida(data[0],new string('0',64),cert);}catch{fals=true;}Check(fals,"rebutja certificat que no coincidix");
   }
   byte[] exe=File.ReadAllBytes(file);
   string tmp=Path.Combine(args[0],"malformat.exe");
   try {
    int pos=exe.Length-ConnectaCompas.Marca.Length-4;
    for(int i=0;i<4;i++)exe[pos+i]=255;
    File.WriteAllBytes(tmp,exe);bool rebutjat=false;try{ConnectaCompas.LligPaquet(tmp);}catch{rebutjat=true;}Check(rebutjat,"rebutja mida manipulada");
    File.WriteAllText(tmp,"sense paquet",Encoding.UTF8);Check(ConnectaCompas.LligPaquet(tmp)==null,"executable sense dades usa compatibilitat antiga");
   } finally {if(File.Exists(tmp))File.Delete(tmp);}
   return 0;
  }catch(Exception e){Console.Error.WriteLine(e);return 1;}
 }
}
