// Compàs: Gestió lliure per a associacions — la finestra de l'app
// Copyright (C) 2026 José Vicente Sanjuan
// SPDX-License-Identifier: AGPL-3.0-or-later
package io.github.jossanmar9.compas;

import android.Manifest;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Insets;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.print.PrintAttributes;
import android.print.PrintManager;
import android.provider.MediaStore;
import android.text.Html;
import android.view.WindowInsets;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.RenderProcessGoneDetail;
import android.webkit.URLUtil;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.FrameLayout;
import android.widget.Toast;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLDecoder;
import java.nio.charset.StandardCharsets;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Mostra Compàs en un WebView. A més del que fa el navegador, s'encarrega de:
 *  - triar fitxers per a pujar (importacions, logotip, restaurar una còpia…);
 *  - desar les baixades (CSV, còpies, remeses, PDF) a la carpeta Baixades/Compàs;
 *  - imprimir (window.print obri el diàleg d'impressió d'Android, que també desa en PDF);
 *  - fer la còpia de seguretat quan l'app passa a segon pla, si hi ha hagut canvis;
 *  - obrir els enllaços de fora (GitHub, correu…) amb l'app que toque.
 */
public class Principal extends Activity {
    private static final int TRIA_FITXER = 1;
    private static final int PERMIS_DOCUMENTS = 2;

    private WebView web;
    private Servidor srv;
    private ValueCallback<Uri[]> triaFitxer;
    private boolean arrancant;
    private boolean netejaHistorial;

    @Override
    protected void onCreate(Bundle estat) {
        super.onCreate(estat);
        srv = Servidor.de(this);

        FrameLayout marc = new FrameLayout(this);
        marc.setBackgroundColor(getColor(R.color.blau_fosc));
        web = new WebView(this);
        marc.addView(web, new FrameLayout.LayoutParams(FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.MATCH_PARENT));
        setContentView(marc);
        // Android 15 dibuixa de vora a vora: es deixa lloc per a les barres del sistema i el teclat
        if (Build.VERSION.SDK_INT >= 30) {
            marc.setOnApplyWindowInsetsListener((v, ins) -> {
                Insets i = ins.getInsets(WindowInsets.Type.systemBars() | WindowInsets.Type.ime() | WindowInsets.Type.displayCutout());
                v.setPadding(i.left, i.top, i.right, i.bottom);
                return WindowInsets.CONSUMED;
            });
        }

        WebSettings s = web.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setAllowFileAccess(false);
        s.setAllowContentAccess(false);
        s.setBuiltInZoomControls(true);
        s.setDisplayZoomControls(false);
        s.setSupportMultipleWindows(false);
        s.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        web.addJavascriptInterface(new Pont(), "CompasAndroid");
        web.setWebViewClient(new Client());
        web.setWebChromeClient(new Chrome());
        web.setDownloadListener((url, agent, disposicio, tipus, mida) -> baixa(url));

        if (Build.VERSION.SDK_INT == 29 && checkSelfPermission(Manifest.permission.WRITE_EXTERNAL_STORAGE) != PackageManager.PERMISSION_GRANTED) {
            requestPermissions(new String[]{Manifest.permission.WRITE_EXTERNAL_STORAGE}, PERMIS_DOCUMENTS);
        }
        arranca(null);
    }

    /** Arranca el servidor (si cal) i obri Compàs; «cami» és la pàgina on es tornarà (o null, l'inici) */
    private void arranca(String cami) {
        if (arrancant) {
            return;
        }
        arrancant = true;
        mostra(pantalla(getString(R.string.carregant), srv.calDesempaquetar() ? getString(R.string.primera_vegada) : "", null));
        new Thread(() -> {
            try {
                srv.arranca();
                runOnUiThread(() -> {
                    CookieManager cm = CookieManager.getInstance();
                    cm.setAcceptCookie(true);
                    cm.setCookie(srv.url(), "compas_app=" + srv.tokenApp() + "; Path=/; SameSite=Strict; HttpOnly");
                    cm.flush();
                    netejaHistorial = true;
                    web.loadUrl(srv.url() + (cami != null ? cami : ""));
                    arrancant = false;
                });
            } catch (IOException e) {
                runOnUiThread(() -> {
                    mostra(pantalla(getString(R.string.error_titol), getString(R.string.error_text), e.getMessage()));
                    arrancant = false;
                });
            }
        }, "arranca-compas").start();
    }

    private void mostra(String html) {
        web.loadDataWithBaseURL(null, html, "text/html", "UTF-8", null);
    }

    private static String pantalla(String titol, String text, String detall) {
        return "<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1'>"
                + "<style>body{font-family:sans-serif;background:#13254a;color:#fff;display:flex;flex-direction:column;justify-content:center;"
                + "min-height:90vh;margin:0;padding:24px;text-align:center}h1{font-size:1.4rem}p{color:#c9d3e6}"
                + "pre{text-align:left;white-space:pre-wrap;font-size:.75rem;background:#0c1830;padding:12px;border-radius:8px;color:#e6ebf5}</style></head>"
                + "<body><h1>" + Html.escapeHtml(titol) + "</h1><p>" + Html.escapeHtml(text) + "</p>"
                + (detall != null ? "<pre>" + Html.escapeHtml(detall) + "</pre>" : "") + "</body></html>";
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Android pot aturar PHP mentre l'app és en segon pla: es torna a arrancar on s'estava
        if (!arrancant && !srv.viu()) {
            String url = web.getUrl();
            String cami = null;
            if (url != null && url.startsWith("http://127.0.0.1:")) {
                Uri u = Uri.parse(url);
                cami = u.getEncodedQuery() != null ? "?" + u.getEncodedQuery() : null;
            }
            arranca(cami);
        }
    }

    @Override
    protected void onStop() {
        super.onStop();
        new Thread(srv::copiaSiCal, "copia-compas").start();
    }

    @Override
    protected void onDestroy() {
        if (isFinishing()) {
            Servidor s = srv;
            new Thread(() -> {
                s.copiaSiCal();
                s.atura();
            }, "atura-compas").start();
        }
        web.destroy();
        super.onDestroy();
    }

    @Override
    @SuppressWarnings("deprecation")
    public void onBackPressed() {
        if (web.canGoBack()) {
            web.goBack();
        } else {
            super.onBackPressed();
        }
    }

    /* ---------------- Enllaços i pàgines ---------------- */

    private class Client extends WebViewClient {
        @Override
        public boolean shouldOverrideUrlLoading(WebView v, WebResourceRequest req) {
            Uri u = req.getUrl();
            if ("127.0.0.1".equals(u.getHost())) {
                return false;
            }
            // La resta (web de Compàs, GitHub, correu…) amb l'app que toque
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, u));
            } catch (ActivityNotFoundException e) {
                Toast.makeText(Principal.this, R.string.sense_app, Toast.LENGTH_SHORT).show();
            }
            return true;
        }

        @Override
        public void onPageFinished(WebView v, String url) {
            // Que «enrere» no torne a la pantalla de càrrega
            if (netejaHistorial && url != null && url.startsWith("http://127.0.0.1:")) {
                v.clearHistory();
                netejaHistorial = false;
            }
            // Imprimir: el diàleg d'Android (impressora o «Desa com a PDF»)
            v.evaluateJavascript("window.print=function(){CompasAndroid.imprimeix(document.title||'Compàs')};", null);
        }

        @Override
        public boolean onRenderProcessGone(WebView v, RenderProcessGoneDetail d) {
            recreate();
            return true;
        }
    }

    private class Pont {
        @JavascriptInterface
        public void imprimeix(String titol) {
            runOnUiThread(() -> {
                PrintManager pm = getSystemService(PrintManager.class);
                String nom = titol == null || titol.isEmpty() ? "Compàs" : titol;
                pm.print(nom, web.createPrintDocumentAdapter(nom), new PrintAttributes.Builder().build());
            });
        }
    }

    /* ---------------- Pujar fitxers ---------------- */

    private class Chrome extends WebChromeClient {
        @Override
        public boolean onShowFileChooser(WebView v, ValueCallback<Uri[]> cb, FileChooserParams p) {
            if (triaFitxer != null) {
                triaFitxer.onReceiveValue(null);
            }
            triaFitxer = cb;
            Intent i = new Intent(Intent.ACTION_GET_CONTENT);
            i.addCategory(Intent.CATEGORY_OPENABLE);
            i.setType("*/*");
            if (p.getMode() == FileChooserParams.MODE_OPEN_MULTIPLE) {
                i.putExtra(Intent.EXTRA_ALLOW_MULTIPLE, true);
            }
            try {
                startActivityForResult(i, TRIA_FITXER);
            } catch (ActivityNotFoundException e) {
                triaFitxer = null;
                cb.onReceiveValue(null);
                Toast.makeText(Principal.this, R.string.sense_app, Toast.LENGTH_SHORT).show();
            }
            return true;
        }
    }

    @Override
    @SuppressWarnings("deprecation")
    protected void onActivityResult(int codi, int resultat, Intent dades) {
        if (codi != TRIA_FITXER || triaFitxer == null) {
            super.onActivityResult(codi, resultat, dades);
            return;
        }
        Uri[] triats = null;
        if (resultat == RESULT_OK && dades != null) {
            if (dades.getClipData() != null) {
                triats = new Uri[dades.getClipData().getItemCount()];
                for (int k = 0; k < triats.length; k++) {
                    triats[k] = dades.getClipData().getItemAt(k).getUri();
                }
            } else if (dades.getData() != null) {
                triats = new Uri[]{dades.getData()};
            }
        }
        triaFitxer.onReceiveValue(triats);
        triaFitxer = null;
    }

    /* ---------------- Baixades ---------------- */

    /** Torna a demanar el fitxer (amb les galetes de la sessió) i el desa a Baixades/Compàs */
    private void baixa(String url) {
        new Thread(() -> {
            String nom = null;
            Uri desat = null;
            String tipus = "application/octet-stream";
            try {
                HttpURLConnection c = (HttpURLConnection) new URL(url).openConnection();
                c.setRequestProperty("Cookie", CookieManager.getInstance().getCookie(url));
                c.setConnectTimeout(5000);
                c.setReadTimeout(120000);
                if (c.getResponseCode() != 200) {
                    throw new IOException("HTTP " + c.getResponseCode());
                }
                if (c.getContentType() != null) {
                    tipus = c.getContentType().split(";")[0].trim();
                }
                nom = nomFitxer(c.getHeaderField("Content-Disposition"), url, tipus);
                ContentResolver cr = getContentResolver();
                ContentValues v = new ContentValues();
                v.put(MediaStore.Downloads.DISPLAY_NAME, nom);
                v.put(MediaStore.Downloads.MIME_TYPE, tipus);
                v.put(MediaStore.Downloads.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS + "/Compàs");
                v.put(MediaStore.Downloads.IS_PENDING, 1);
                desat = cr.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, v);
                if (desat == null) {
                    throw new IOException("MediaStore");
                }
                try (InputStream in = c.getInputStream(); OutputStream out = cr.openOutputStream(desat)) {
                    Servidor.copia(in, out);
                }
                v.clear();
                v.put(MediaStore.Downloads.IS_PENDING, 0);
                cr.update(desat, v, null, null);
                c.disconnect();
            } catch (Exception e) {
                if (desat != null) {
                    getContentResolver().delete(desat, null, null);
                }
                runOnUiThread(() -> Toast.makeText(this, R.string.baixada_error, Toast.LENGTH_LONG).show());
                return;
            }
            final String n = nom;
            final Uri u = desat;
            final String t = tipus;
            runOnUiThread(() -> {
                Toast.makeText(this, getString(R.string.baixada_desada, n), Toast.LENGTH_LONG).show();
                // Els PDF s'obrin de seguida (el WebView no els sap mostrar)
                if ("application/pdf".equals(t)) {
                    try {
                        startActivity(new Intent(Intent.ACTION_VIEW).setDataAndType(u, t).addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION));
                    } catch (ActivityNotFoundException ignored) {
                        // Queda a Baixades
                    }
                }
            });
        }, "baixada-compas").start();
    }

    private static final Pattern NOM_UTF8 = Pattern.compile("filename\\*=UTF-8''([^;]+)", Pattern.CASE_INSENSITIVE);
    private static final Pattern NOM = Pattern.compile("filename=\"([^\"]+)\"", Pattern.CASE_INSENSITIVE);

    static String nomFitxer(String disposicio, String url, String tipus) {
        if (disposicio != null) {
            Matcher m = NOM_UTF8.matcher(disposicio);
            if (m.find()) {
                try {
                    return net(URLDecoder.decode(m.group(1).trim(), StandardCharsets.UTF_8.name()));
                } catch (Exception ignored) {
                    // es prova l'altra forma
                }
            }
            m = NOM.matcher(disposicio);
            if (m.find()) {
                return net(m.group(1));
            }
        }
        return net(URLUtil.guessFileName(url, disposicio, tipus));
    }

    private static String net(String nom) {
        String n = nom.replaceAll("[\\\\/:*?\"<>|\\x00-\\x1f]", "_").trim();
        return n.isEmpty() ? "compas" : n;
    }
}
