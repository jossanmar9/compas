# Genera la guia il·lustrada de xarxa local. Requisit: reportlab==4.4.9.
# SPDX-License-Identifier: AGPL-3.0-or-later
from pathlib import Path
from reportlab.pdfgen import canvas
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import Paragraph
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.utils import ImageReader
import argparse


ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser()
parser.add_argument('--output',default=str(ROOT/'docs/pdf/guia-xarxa-local.pdf'))
parser.add_argument('--render',action='store_true')
args=parser.parse_args()
OUT=Path(args.output).resolve();OUT.parent.mkdir(parents=True,exist_ok=True)
CAP=ROOT/'docs/imatges/xarxa'
for name,file in [('Regular','regular'),('Bold','bold'),('Italic','italic'),('Mono','regular')]:
    pdfmetrics.registerFont(TTFont(name,str(ROOT/'eines/fonts'/('atkinson-hyperlegible-'+file+'.ttf'))))
pdfmetrics.registerFontFamily('Regular',normal='Regular',bold='Bold',italic='Italic',boldItalic='Bold')
W,H=A4; M=44; CW=W-2*M
NAVY=colors.HexColor('#07345B'); BLUE=colors.HexColor('#087BBE'); LIGHT=colors.HexColor('#EDF5FB'); INK=colors.HexColor('#18344C'); GREY=colors.HexColor('#52697B'); ORANGE=colors.HexColor('#F0AD27'); LINE=colors.HexColor('#D8E5EF')
c=canvas.Canvas(str(OUT),pagesize=A4,pageCompression=1)
c.setTitle('Compàs en xarxa local - Guia pas a pas');c.setAuthor('Compàs');c.setSubject('Configuració senzilla del servidor i dels equips clients. Referència: Compàs 2.3.2.')
page=0;y=0

def p(text,size=10.8,leading=15.6,space=9,color=INK,font='Regular',x=M,width=CW):
    global y
    st=ParagraphStyle('p',fontName=font,fontSize=size,leading=leading,textColor=color,spaceAfter=0)
    q=Paragraph(text,st);_,h=q.wrap(width,1000)
    if y-h<48:raise RuntimeError(f'Pagina {page} sense espai: {text[:65]} y={y} h={h}')
    q.drawOn(c,x,y-h);y-=h+space

def box(text,title=None,bg=LIGHT):
    global y
    st=ParagraphStyle('box',fontName='Regular',fontSize=10.8,leading=15.7,textColor=INK)
    q=Paragraph((('<b>'+title+'</b><br/><br/>') if title else '')+text,st);_,h=q.wrap(CW-28,1000)
    height=h+26
    if y-height<48:raise RuntimeError(f'Box massa baixa, pagina {page}')
    c.setFillColor(bg);c.roundRect(M,y-height,CW,height,9,fill=1,stroke=0)
    c.setFillColor(BLUE);c.roundRect(M,y-height,3,height,1.5,fill=1,stroke=0)
    q.drawOn(c,M+14,y-height+13);y-=height+13

def start(kicker,title,subtitle=None):
    global page,y
    if page:c.showPage()
    page+=1
    c.setFillColor(BLUE);c.setFont('Bold',9);c.drawString(M,H-31,'COMPÀS  /  XARXA LOCAL')
    c.setStrokeColor(LINE);c.line(M,H-41,W-M,H-41)
    c.setFont('Regular',8.3);c.setFillColor(GREY);c.drawString(M,28,'Guia pràctica · Compàs 2.3.2 · Octubre de 2026')
    c.drawRightString(W-M,28,f'{page} / 10')
    y=H-72
    p(kicker.upper(),9.2,12,8,BLUE,'Bold')
    p(title,25,29,14,NAVY,'Bold')
    if subtitle:p(subtitle,11.6,17,16,GREY)

def sub(title):
    global y
    y-=3;p(title,14,18,8,NAVY,'Bold')

def step(n,title,text):
    global y
    p(f'<font color="#087BBE"><b>{n}.</b></font> <b>{title}</b><br/>'+text,11.2,16,11)

def fig(filename,width=None,crop_top=None,caption='',rect=None,crop_box=None):
    global y
    im=ImageReader(str(CAP/filename));iw,ih=im.getSize()
    left,top,cropw,ch=crop_box or (0,0,iw,crop_top or ih)
    dw=width or CW;scale=dw/cropw;dh=scale*ch;x=(W-dw)/2
    if y-dh<58:raise RuntimeError(f'Figura massa gran pagina {page}')
    c.saveState();path=c.beginPath();path.rect(x,y-dh,dw,dh);c.clipPath(path,stroke=0,fill=0)
    c.drawImage(im,x-left*scale,y+(top-ih)*scale,width=iw*scale,height=ih*scale,mask='auto')
    c.restoreState();c.setStrokeColor(LINE);c.setLineWidth(.65);c.rect(x,y-dh,dw,dh,stroke=1,fill=0)
    if rect:
        rx,ry,rw,rh=rect;s=dw/iw;c.setStrokeColor(ORANGE);c.setLineWidth(2);c.roundRect(x+rx*s,y-(ry+rh)*s,rw*s,rh*s,3,stroke=1,fill=0)
    y-=dh+8
    if caption:p(caption,9.1,12.3,13,GREY,'Italic',x=x,width=dw)

def code(text):
    global y
    c.setFillColor(NAVY);c.roundRect(M,y-35,CW,35,6,fill=1,stroke=0)
    c.setFont('Mono',11);c.setFillColor(colors.white);c.drawString(M+13,y-22,text);y-=47

def rule():
    global y
    c.setStrokeColor(LINE);c.line(M,y,W-M,y);y-=18

def screen(cx,cy,label,server=False):
    width=94;height=62
    c.setFillColor(colors.white);c.setStrokeColor(BLUE if server else GREY);c.setLineWidth(1.7)
    c.roundRect(cx-width/2,cy,width,height,6,fill=1,stroke=1)
    c.setFillColor(LIGHT);c.roundRect(cx-width/2+6,cy+9,width-12,height-17,3,fill=1,stroke=0)
    c.setStrokeColor(BLUE if server else GREY);c.line(cx,cy,cx,cy-10);c.line(cx-17,cy-10,cx+17,cy-10)
    c.setFont('Bold',10.1);c.setFillColor(NAVY);c.drawCentredString(cx,cy-27,label)

# 1
start('Guia pas a pas','Compàs en xarxa local','Configura el servidor una vegada. Després, cada persona entra amb el navegador i el seu compte.')
c.drawImage(str(ROOT/'launcher/installador.png'),W-M-52,H-137,width=52,height=52,mask='auto')
y-=14
p('Una adreça fàcil de recordar',15,20,10,NAVY,'Bold')
box('<font size="22"><b>https://compas.local</b></font><br/><br/>Sense IP fixa i sense haver de compartir carpetes ni la base de dades.')
y-=15
cy=y-92
c.setStrokeColor(LINE);c.setLineWidth(2);c.line(130,cy+26,465,cy+26)
screen(125,cy,'Equip client');screen(W/2,cy+9,'Servidor',True);screen(W-125,cy,'Equip client')
y=cy-52
p('El servidor és l’ordinador que guarda les dades. Els altres equips les consulten o les modifiquen segons els permisos de cada persona.',12,17,19)
box('La preparació inicial es fa al servidor i, una sola vegada, en cada equip client. Després, l’ús habitual és obrir la URL i entrar.','Què aconseguiràs amb esta guia')
p('Amb captures de Windows, instruccions per a Linux i solucions als problemes més habituals.',10.5,15,9,GREY)
p('Referència: versió 2.3.2. Les captures utilitzen una instal·lació de prova i dades inventades.',9.7,13.5,0,GREY,'Italic')

# 2
start('Abans de començar','Tria quin ordinador farà de servidor')
p('Pensa en el servidor com en l’ordinador de referència de l’associació: és on està instal·lat Compàs i on es guarda la informació. Quan algú entra des d’un altre equip, continua treballant amb eixes mateixes dades.')
sub('Què necessites')
step(1,'Un servidor disponible','Tria un ordinador que puga quedar encés mentre treballe la resta. Si és un portàtil, connecta’l al corrent i evita que entre en suspensió durant eixe temps.')
step(2,'La mateixa xarxa privada','El servidor i els clients han d’estar connectats a la xarxa de l’associació, per Wi-Fi o per cable. Una xarxa de convidats pot impedir que es troben entre ells.')
step(3,'Compàs actualitzat i una còpia de seguretat','Utilitza la versió 2.3.2 o una de posterior. Abans d’activar la xarxa, comprova que pots entrar i fes una còpia de seguretat.')
sub('En Windows, comprova el perfil de xarxa')
p('Obri la configuració de Windows, entra en <b>Xarxa i Internet</b> i busca les propietats de la connexió que utilitzes. El perfil ha d’estar marcat com a <b>Privada</b>. Els noms dels menús poden variar un poc segons la versió de Windows.')
box('Fes-ho només en una xarxa que conegues i controles, com la de l’associació. No marques com a privada una Wi-Fi pública.','Xarxa de confiança')
sub('Com està organitzada la guia')
p('<b>Servidor Windows:</b> pàgines 3 a 5.<br/><b>Altres equips Windows:</b> pàgines 6 i 7.<br/><b>Ús diari i ajuda:</b> pàgines 8 i 9.<br/><b>Linux:</b> pàgina 10.',11,17)

# 3
start('Servidor Windows · Pas 1','Obri l’assistent des d’Inici')
p('Fes-ho en l’ordinador que guardarà les dades. Pots utilitzar un compte d’administració o de gestió; el consultor no té accés a esta configuració.')
step(1,'Entra en Compàs i ves a Inici','En la part de dalt trobaràs l’apartat <b>Xarxa local / HTTPS</b>.')
fig('01-inici-xarxa.png',width=CW,caption='Captura real del nou apartat d’Inici en mode local.')
step(2,'Prem Configura la xarxa local','S’obrirà l’assistent del servidor. Si queda darrere, busca el panell de Compàs en la barra de tasques.')
fig('02-configura.jpg',width=380,caption='L’assistent on activaràs compartir i triaràs el nom.')
box('També pots obrir-lo des de la icona de Compàs al costat del rellotge: botó dret i <b>Xarxa local / HTTPS…</b>.','Un altre accés al mateix assistent')

# 4
start('Servidor Windows · Pas 2','Activa compartir i confirma el certificat')
step(1,'Marca Compartir Compàs en la xarxa privada','Deixa el nom <b>compas.local</b>. Si ja hi ha un altre servidor Compàs en la mateixa xarxa, tria un nom diferent acabat en <b>.local</b>, com <b>compas-musica.local</b>.')
fig('02-configura.jpg',width=400,caption='El nom triat serà el que utilitzaran tots els equips.',rect=(16,99,429,26))
step(2,'Prem Desa i reinicia','Compàs et demanarà permís per a preparar el tallafoc. Confirma l’avís de Windows si l’has iniciat tu: permet la connexió des de la xarxa privada.')
step(3,'Confirma el certificat del teu servidor','En tornar a arrancar, Compàs pot preguntar-te si vols confiar en el certificat HTTPS que acaba de crear. Mostra l’adreça i una empremta SHA-256. Si estàs configurant el teu servidor i l’adreça és la que has triat, confirma-ho.')
box('El certificat permet xifrar la connexió. Cada instal·lació en té un de propi. L’empremta és la seua identificació: la compararem en preparar els altres equips.','Què és eixe certificat?')
p('Deixa el panell del servidor obert. No has de buscar ni fixar cap adreça IP, ni obrir ports del router cap a Internet.',11,16)

# 5
start('Servidor Windows · Pas 3','Prepara un compte per a cada persona')
p('Entra com a administrador i ves a <b>Administració / Usuaris</b>. Prem <b>Nou</b> i crea els comptes que necessiteu. Cada persona ha d’utilitzar el seu.')
fig('04-usuari-detall.png',width=CW,crop_top=680,caption='Detall real del formulari. «maria.garcia» és només un nom d’exemple.')
step(1,'Escriu un nom senzill','Pot tindre de 3 a 40 caràcters: lletres sense accents, números, punt, guió i guió baix. No es poden repetir noms, encara que canvies les majúscules.')
step(2,'Tria què podrà fer eixa persona','<b>Administrador/a:</b> gestiona l’aplicació i els comptes.<br/><b>Usuari/ària:</b> fa la gestió diària.<br/><b>Consultor/a:</b> té consulta limitada; no pot modificar dades ni accedir a economia, IBAN, exportacions, documents descarregables o còpies.')
step(3,'Posa una contrasenya i desa','Comunica el nom i la contrasenya a la persona corresponent per un canal de confiança. No els inclogues en un missatge general de l’associació.')

# 6
start('Altres equips Windows · Pas 4','Prepara el paquet per als clients')
p('Este pas encara es fa en el servidor. En el seu panell, prem <b>Preparar altres equips</b>. Compàs et mostrarà l’adreça i l’empremta del certificat i obrirà la carpeta del paquet.')
box('<font size="17"><b>Client-Compas.zip</b></font><br/><br/>Este és el fitxer que has de passar als altres ordinadors. Pots utilitzar, per exemple, un USB.','El paquet que necessites')
p('El ZIP porta el certificat públic i un assistent de connexió. No conté la base de dades de l’associació ni les claus privades del servidor.')
sub('Ara, en cada ordinador client')
step(1,'Copia el ZIP a l’ordinador','Assegura’t que el paquet te l’ha donat la persona responsable del servidor.')
step(2,'Descomprimeix-lo completament','Prem el ZIP amb el botó dret i tria <b>Extrau-ho tot</b> o l’opció equivalent. Obri la carpeta que s’ha creat.')
step(3,'Obri Connecta-Compas.exe','Executa’l des de la carpeta descomprimida, junt amb els altres fitxers. No cal instal·lar Compàs complet en este ordinador.')
box('Si Windows mostra un avís sobre l’origen del programa, comprova amb el responsable que el paquet és el que ha preparat el servidor abans de continuar.','Si apareix un avís')
p('No copies el fitxer de la base de dades ni compartisques la carpeta sencera de dades. Per a preparar el client, utilitza el ZIP que crea Compàs.',10.7,15.5)

# 7
start('Altres equips Windows · Pas 5','Compara l’empremta i entra')
p('L’assistent et mostra l’adreça del servidor i una empremta SHA-256. Compara-la amb la que apareix en <b>Preparar altres equips</b> del servidor: ha de coincidir <b>tota</b>.')
box('<b>Servidor:</b> mostra la seua empremta.<br/><b>Equip client:</b> ha de mostrar exactament la mateixa.<br/><br/>Si no coincidixen, no confirmes. Demana al responsable que revise el paquet.','Una comprovació que només fas la primera vegada')
p('Quan l’adreça i l’empremta siguen correctes, confirma que confies en el certificat. L’assistent obrirà el navegador. Escriu el teu usuari i la contrasenya i prem <b>Entra</b>.')
fig('07-entrada.png',width=350,crop_box=(300,490,680,470),caption='Detall real de l’entrada. El nom és un exemple: utilitza el teu compte.')
p('A partir d’ara, obri <b>https://compas.local</b> o guarda-la com a favorita. Si heu triat un altre nom, utilitza eixa adreça. No cal executar l’assistent cada dia.',11,16)

# 8
start('Quan ja funciona','Treballar cada dia és molt senzill')
sub('En el servidor')
p('Obri Compàs i deixa el seu panell funcionant. Mantín l’ordinador encés i connectat a la xarxa mentre altres persones treballen. Si entra en suspensió o s’apaga, els clients deixaran de poder accedir-hi.')
box('El servidor pot treballar en la finestra pròpia de Compàs, també amb LAN activat. Tancar eixa finestra de treball no atura el servei compartit. El panell menut continua en marxa; utilitza <b>Atura i tanca</b> quan vulgues parar-lo per a tots.','Panell del servidor i finestra de treball')
sub('En els altres equips')
p('Obri l’adreça guardada, entra amb el teu compte i treballa. Pots tancar el navegador quan acabes: això no atura el servidor ni desconnecta les altres persones.')
sub('Quan vulgues parar el servidor')
p('Avisa primer els altres usuaris perquè acaben el que estiguen fent. Després utilitza <b>Atura i tanca</b>. Compàs intentarà fer la còpia de seguretat abans de parar. En Linux, el tancament es fa des del terminal del servidor amb <b>Ctrl+C</b>.')
sub('Continua fent còpies de seguretat')
p('Treballar en xarxa no substituïx les còpies. Comprova-les i conserva també una còpia externa protegida. La configuració dels certificats es guarda a banda de la base de dades: si has de canviar o recuperar el servidor, demana que es revise també eixa carpeta.')
sub('Per a tornar al mode local')
p('En Windows, ves a <b>Inici / Configura la xarxa local</b>, desmarca compartir i prem <b>Desa i reinicia</b>. En Linux, torna a executar l’assistent i tria no compartir. Cal fer-ho abans de restaurar una còpia o buidar les dades de gestió.')

# 9
start('Si alguna cosa no va bé','Comença per estes comprovacions')
step(1,'El navegador diu que no troba compas.local','Comprova que el servidor està encés i que el seu panell continua obert. Revisa que els dos equips estiguen en la mateixa xarxa i que has escrit l’adreça completa, començant per <b>https://</b>.')
step(2,'Funciona al servidor, però no en els altres equips','Comprova el perfil <b>Privada</b> de Windows. Una Wi-Fi de convidats o una opció d’aïllament entre equips pot impedir la connexió. Demana al responsable de la xarxa que ho revise.')
step(3,'El navegador mostra un avís de certificat','Torna a comprovar el paquet i l’empremta amb el responsable. No ignores l’avís. Si el servidor s’ha canviat o s’han perdut els certificats, pot caldre preparar de nou el client.')
step(4,'L’assistent diu que falten fitxers','Descomprimeix <b>tot</b> el ZIP i executa <b>Connecta-Compas.exe</b> des d’eixa carpeta. El programa necessita els altres fitxers que l’acompanyen.')
step(5,'El servidor HTTPS no arranca','Copia el missatge exacte i consulta’l amb la persona responsable. Pot haver-hi una altra aplicació utilitzant la mateixa connexió. No desactives el tallafoc ni òbrigues el router a Internet per a provar.')
step(6,'Un usuari no veu economia o no pot editar','Revisa el seu rol a <b>Usuaris</b>. És normal si és un compte de consulta. Si no pot entrar, comprova el nom i demana a l’administrador que li prepare una contrasenya nova.')
box('El nom local es troba amb un sistema anomenat mDNS. Algunes xarxes, VPN o equips Linux el poden bloquejar o no tindre’l preparat. En eixe cas cal revisar la xarxa, no canviar la base de dades.','Si el nom continua sense trobar-se')

# 10
start('Si utilitzes Linux','El mateix funcionament, amb un assistent al terminal')
p('Les captures de la guia són de Windows. En Linux, la idea és la mateixa: preparar el servidor, confirmar el certificat una vegada en cada client i entrar després per la URL.')
sub('Preparar un servidor Linux')
step(1,'Obri l’assistent','Des d’<b>Inici</b>, prem <b>Configura la xarxa local</b>. També pots buscar <b>Compàs · Xarxa local HTTPS</b> en el menú d’aplicacions o escriure en un terminal:')
code('compas --xarxa')
step(2,'Respon les preguntes','Escriu <b>si</b> quan vulgues activar compartir. Deixa <b>compas.local</b> com a nom o tria’n un altre. L’assistent pot demanar la contrasenya d’administració per a preparar la connexió, el certificat i el tallafoc.')
step(3,'Tanca i torna a obrir Compàs','Deixa el terminal del servidor obert mentre els altres equips treballen. El paquet per als clients es guarda habitualment en:')
code('~/.local/share/compas/dades/https/Client-Compas.zip')
sub('Preparar un client Linux')
p('Descomprimeix el ZIP en una carpeta. Obri un terminal dins d’eixa carpeta i executa:')
code('bash connecta.sh')
p('Compara l’empremta amb el servidor i confirma-la només si coincidix. L’assistent instal·larà el certificat i pot demanar la contrasenya d’administració. Després obri <b>https://compas.local</b>.')
box('En Ubuntu, Debian o Linux Mint poden fer falta <b>libcap2-bin</b>, <b>ca-certificates</b> i <b>openssl</b>. Per a trobar noms .local, el client necessita Avahi/libnss-mdns o un sistema equivalent. Si un navegador té un magatzem propi, pot caldre importar-hi <b>compas-ca.crt</b>. Demana ajuda per a este pas si l’assistent avisa.','Si l’assistent indica que falta algun component')
p('Guia del projecte: <link href="https://github.com/jossanmar9/compas/blob/main/docs/xarxa-local.md" color="#087BBE">github.com/jossanmar9/compas · xarxa local</link>',9.5,13,0,GREY)

assert page==10
c.save()
if args.render:
    import pypdfium2 as pdfium
    folder=ROOT/'tmp/pdfs/render';folder.mkdir(parents=True,exist_ok=True)
    doc=pdfium.PdfDocument(str(OUT))
    for i in range(len(doc)):
        doc[i].render(scale=1.25).to_pil().save(folder/f'pagina-{i+1:02}.png')
print(f'{OUT}\n{page} pagines')
