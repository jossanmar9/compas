; Instal·lador de Compàs (Inno Setup 6)
; Es compila des de GitHub Actions:  iscc /DVersio=1.0.0 /DOrigen=..\dist\Compas launcher\compas.iss

#ifndef Versio
  #define Versio "0.0.0"
#endif
#ifndef VersioNum
  #define VersioNum "0.0.0"
#endif
#ifndef Origen
  #define Origen "..\dist\Compas"
#endif

[Setup]
AppId={{6F3B2C1A-7E54-4C2B-9A1E-C0A5C0A5C0A5}
AppName=Compàs
AppVersion={#Versio}
AppVerName=Compàs {#Versio}
AppPublisher=José Vicente Sanjuan
AppComments=Gestió lliure per a associacions
; Nom i versió a les propietats de l'instal·lador (Windows: botó dret → Propietats → Detalls)
VersionInfoVersion={#VersioNum}
VersionInfoProductName=Compàs
VersionInfoProductVersion={#VersioNum}
VersionInfoProductTextVersion={#Versio}
VersionInfoDescription=Instal·lador de Compàs: gestió lliure per a associacions
VersionInfoCompany=Compàs
VersionInfoCopyright=© 2026 José Vicente Sanjuan · AGPL-3.0-or-later
DefaultDirName={localappdata}\Programs\Compas
DefaultGroupName=Compàs
DisableProgramGroupPage=yes
PrivilegesRequired=lowest
OutputDir=..\dist
OutputBaseFilename=Compas-{#Versio}-installador
SetupIconFile=installador.ico
UninstallDisplayIcon={app}\Compas.exe
Compression=lzma2/max
SolidCompression=yes
WizardStyle=modern
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible

[Languages]
Name: "ca"; MessagesFile: "compiler:Languages\Catalan.isl"; LicenseFile: "llicencia-ca.txt"; InfoBeforeFile: "privacitat-ca.txt"
Name: "es"; MessagesFile: "compiler:Languages\Spanish.isl"; LicenseFile: "llicencia-es.txt"; InfoBeforeFile: "privacitat-es.txt"

[Tasks]
Name: "escriptori"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"

[Files]
Source: "{#Origen}\*"; DestDir: "{app}"; Excludes: "data\*,compas.ini"; Flags: recursesubdirs createallsubdirs ignoreversion
; compas.ini no se sobreescriu si ja existeix (conserva la configuració)
Source: "{#Origen}\compas.ini"; DestDir: "{app}"; Flags: onlyifdoesntexist uninsneveruninstall

[Dirs]
; Les dades no s'esborren mai en desinstal·lar
Name: "{app}\data"; Flags: uninsneveruninstall

[Icons]
Name: "{group}\Compàs"; Filename: "{app}\Compas.exe"
Name: "{group}\Manual de Compàs"; Filename: "{app}\docs\pdf\manual-usuari.pdf"; Languages: ca
Name: "{group}\Manual de Compàs"; Filename: "{app}\docs\pdf\manual-usuario.pdf"; Languages: es
Name: "{group}\Manual d'administració de Compàs"; Filename: "{app}\docs\pdf\manual-administracio.pdf"; Languages: ca
Name: "{group}\Manual de administración de Compàs"; Filename: "{app}\docs\pdf\manual-administracion.pdf"; Languages: es
Name: "{autodesktop}\Compàs"; Filename: "{app}\Compas.exe"; Tasks: escriptori

[UninstallDelete]
; Configuració de la finestra de Compàs (WebView2, o Edge o Chrome en mode aplicació). Les dades de Compàs no es toquen.
Type: filesandordirs; Name: "{localappdata}\Compas\visor"
Type: filesandordirs; Name: "{localappdata}\Compas\finestra"
Type: files; Name: "{localappdata}\Compas\finestra.txt"
Type: dirifempty; Name: "{localappdata}\Compas"

[Run]
Filename: "{app}\Compas.exe"; Description: "{cm:LaunchProgram,Compàs}"; Flags: nowait postinstall skipifsilent
