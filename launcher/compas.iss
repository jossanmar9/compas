; Instal·lador de Compàs (Inno Setup 6)
; Es compila des de GitHub Actions:  iscc /DVersio=1.0.0 /DOrigen=..\dist\Compas launcher\compas.iss

#ifndef Versio
  #define Versio "0.0.0"
#endif
#ifndef Origen
  #define Origen "..\dist\Compas"
#endif

[Setup]
AppId={{6F3B2C1A-7E54-4C2B-9A1E-C0A5C0A5C0A5}
AppName=Compàs
AppVersion={#Versio}
AppVerName=Compàs {#Versio}
AppPublisher=Compàs
AppComments=Gestió lliure per a societats musicals
DefaultDirName={localappdata}\Programs\Compas
DefaultGroupName=Compàs
DisableProgramGroupPage=yes
PrivilegesRequired=lowest
OutputDir=..\dist
OutputBaseFilename=Compas-{#Versio}-installador
SetupIconFile=compas.ico
UninstallDisplayIcon={app}\Compas.exe
LicenseFile=..\LICENSE
Compression=lzma2/max
SolidCompression=yes
WizardStyle=modern
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible

[Languages]
Name: "ca"; MessagesFile: "compiler:Languages\Catalan.isl"
Name: "es"; MessagesFile: "compiler:Languages\Spanish.isl"

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
Name: "{group}\Manual"; Filename: "{app}\docs"
Name: "{autodesktop}\Compàs"; Filename: "{app}\Compas.exe"; Tasks: escriptori

[Run]
Filename: "{app}\Compas.exe"; Description: "{cm:LaunchProgram,Compàs}"; Flags: nowait postinstall skipifsilent
