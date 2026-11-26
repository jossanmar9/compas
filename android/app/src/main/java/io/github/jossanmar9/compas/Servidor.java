// Compàs: Gestió lliure per a associacions — servidor de PHP dins del mòbil
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
package io.github.jossanmar9.compas;

import android.content.Context;
import android.content.pm.PackageInfo;
import android.os.Environment;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.RandomAccessFile;
import java.net.HttpURLConnection;
import java.net.InetSocketAddress;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.security.SecureRandom;
import java.util.Map;
import java.util.TimeZone;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

/**
 * Arranca el mateix Compàs de l'ordinador: el PHP per a Android (lib/&lt;abi&gt;/libphp.so) amb el servidor integrat,
 * només a 127.0.0.1 i en un port lliure. Les dades són a files/dades, dins de l'app.
 *
 * Seguretat: qualsevol app del mòbil pot connectar-se a 127.0.0.1, així que el servidor només respon a qui porta
 * la galeta compas_app amb un secret que canvia a cada arrancada (COMPAS_APP_TOKEN, vegeu src/xarxa.php).
 */
final class Servidor {
    private static Servidor instancia;

    private final Context ctx;
    private final File programa;
    private final File dades;
    private Process proc;
    private int port;
    private String tokenApp;
    private String tokenCopia;

    static synchronized Servidor de(Context c) {
        if (instancia == null) {
            instancia = new Servidor(c.getApplicationContext());
        }
        return instancia;
    }

    private Servidor(Context c) {
        ctx = c;
        programa = new File(c.getFilesDir(), "compas");
        dades = new File(c.getFilesDir(), "dades");
    }

    synchronized boolean viu() {
        return proc != null && proc.isAlive();
    }

    String url() {
        return "http://127.0.0.1:" + port + "/";
    }

    String tokenApp() {
        return tokenApp;
    }

    /** Desempaqueta el programa si l'app és nova o s'ha actualitzat */
    boolean calDesempaquetar() {
        return !llig(new File(programa, ".versio")).equals(versioApp());
    }

    private String versioApp() {
        try {
            PackageInfo p = ctx.getPackageManager().getPackageInfo(ctx.getPackageName(), 0);
            return p.getLongVersionCode() + "-" + p.lastUpdateTime;
        } catch (Exception e) {
            return "?";
        }
    }

    synchronized void arranca() throws IOException {
        if (viu()) {
            return;
        }
        if (calDesempaquetar()) {
            desempaqueta();
        }
        if (!dades.isDirectory() && !dades.mkdirs()) {
            throw new IOException("No es pot crear " + dades);
        }
        File temp = new File(ctx.getCacheDir(), "php");
        temp.mkdirs();
        File registre = new File(dades, "servidor.log");
        if (registre.length() > 1024 * 1024) {
            registre.delete();
        }

        port = portLliure();
        tokenApp = secret();
        tokenCopia = secret();
        File php = new File(ctx.getApplicationInfo().nativeLibraryDir, "libphp.so");
        File app = new File(programa, "app");
        ProcessBuilder pb = new ProcessBuilder(php.getAbsolutePath(),
                "-c", new File(programa, "php.ini").getAbsolutePath(),
                "-d", "error_log=" + new File(dades, "php-errors.log").getAbsolutePath(),
                "-d", "sys_temp_dir=" + temp.getAbsolutePath(),
                "-d", "upload_tmp_dir=" + temp.getAbsolutePath(),
                "-d", "opcache.lockfile_path=" + temp.getAbsolutePath(),
                "-S", "127.0.0.1:" + port,
                "-t", app.getAbsolutePath(), new File(app, "index.php").getAbsolutePath());
        pb.directory(programa);
        Map<String, String> env = pb.environment();
        env.put("COMPAS_ANDROID", "1");
        env.put("COMPAS_DADES", dades.getAbsolutePath());
        env.put("COMPAS_TOKEN", tokenCopia);
        env.put("COMPAS_APP_TOKEN", tokenApp);
        env.put("COMPAS_ZONA", TimeZone.getDefault().getID());
        env.put("COMPAS_CARPETA_EXTERNA", new File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOCUMENTS), "Compàs").getAbsolutePath());
        env.put("TMPDIR", temp.getAbsolutePath());
        env.put("HOME", ctx.getFilesDir().getAbsolutePath());
        pb.redirectErrorStream(true);
        pb.redirectOutput(ProcessBuilder.Redirect.appendTo(registre));
        proc = pb.start();

        // Espera que el servidor escolte (fins a 20 s)
        long fi = System.currentTimeMillis() + 20000;
        while (System.currentTimeMillis() < fi) {
            if (!proc.isAlive()) {
                throw new IOException("PHP s'ha aturat (codi " + proc.exitValue() + ")\n\n" + finalRegistre());
            }
            try (Socket s = new Socket()) {
                s.connect(new InetSocketAddress("127.0.0.1", port), 300);
                return;
            } catch (IOException e) {
                try {
                    Thread.sleep(150);
                } catch (InterruptedException ie) {
                    Thread.currentThread().interrupt();
                    throw new IOException("Interromput");
                }
            }
        }
        proc.destroy();
        throw new IOException("El servidor no respon\n\n" + finalRegistre());
    }

    /** Còpia de seguretat si hi ha hagut canvis (com en tancar Compàs a l'ordinador) */
    void copiaSiCal() {
        if (!viu()) {
            return;
        }
        try {
            HttpURLConnection c = (HttpURLConnection) new URL(url() + "?r=copia_tancament").openConnection();
            c.setConnectTimeout(3000);
            c.setReadTimeout(60000);
            c.setRequestProperty("X-Compas-Token", tokenCopia);
            c.setRequestProperty("Cookie", "compas_app=" + tokenApp);
            c.getResponseCode();
            c.disconnect();
        } catch (IOException ignored) {
            // Si no s'ha pogut, es farà la còpia automàtica del dia
        }
    }

    synchronized void atura() {
        if (proc != null) {
            proc.destroy();
            proc = null;
        }
    }

    /** El programa (app, src, docs i php.ini) ve dins de l'APK en assets/compas.zip */
    private void desempaqueta() throws IOException {
        File nou = new File(ctx.getFilesDir(), "compas.nou");
        esborra(nou);
        try (ZipInputStream zip = new ZipInputStream(new BufferedInputStream(ctx.getAssets().open("compas.zip")))) {
            byte[] buf = new byte[64 * 1024];
            String base = nou.getCanonicalPath() + File.separator;
            ZipEntry e;
            while ((e = zip.getNextEntry()) != null) {
                File f = new File(nou, e.getName());
                if (!f.getCanonicalPath().startsWith(base)) {
                    throw new IOException("Fitxer fora de lloc: " + e.getName());
                }
                if (e.isDirectory()) {
                    f.mkdirs();
                    continue;
                }
                f.getParentFile().mkdirs();
                try (OutputStream out = new FileOutputStream(f)) {
                    int n;
                    while ((n = zip.read(buf)) > 0) {
                        out.write(buf, 0, n);
                    }
                }
            }
        }
        escriu(new File(nou, ".versio"), versioApp());
        esborra(programa);
        if (!nou.renameTo(programa)) {
            throw new IOException("No es pot preparar " + programa);
        }
    }

    String finalRegistre() {
        File f = new File(dades, "servidor.log");
        try (RandomAccessFile r = new RandomAccessFile(f, "r")) {
            long n = Math.min(r.length(), 3000);
            byte[] b = new byte[(int) n];
            r.seek(r.length() - n);
            r.readFully(b);
            return new String(b, StandardCharsets.UTF_8);
        } catch (IOException e) {
            return "";
        }
    }

    private static int portLliure() throws IOException {
        try (ServerSocket s = new ServerSocket(0, 1, java.net.InetAddress.getByName("127.0.0.1"))) {
            return s.getLocalPort();
        }
    }

    private static String secret() {
        byte[] b = new byte[24];
        new SecureRandom().nextBytes(b);
        StringBuilder sb = new StringBuilder();
        for (byte x : b) {
            sb.append(String.format("%02x", x));
        }
        return sb.toString();
    }

    private static String llig(File f) {
        try {
            return new String(Files.readAllBytes(f.toPath()), StandardCharsets.UTF_8).trim();
        } catch (IOException e) {
            return "";
        }
    }

    private static void escriu(File f, String s) throws IOException {
        try (OutputStream o = new FileOutputStream(f)) {
            o.write(s.getBytes(StandardCharsets.UTF_8));
        }
    }

    private static void esborra(File f) {
        File[] fills = f.listFiles();
        if (fills != null) {
            for (File c : fills) {
                esborra(c);
            }
        }
        f.delete();
    }

    static void copia(InputStream in, OutputStream out) throws IOException {
        byte[] buf = new byte[64 * 1024];
        int n;
        while ((n = in.read(buf)) > 0) {
            out.write(buf, 0, n);
        }
    }
}
