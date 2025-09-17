# Send WhatsApp

Plugin WordPress che permette di creare link diretti a WhatsApp seguendo le indicazioni ufficiali riportate su [faq.whatsapp.com](https://faq.whatsapp.com/5913398998672934).

## Funzionalità

- Configura il numero di telefono internazionale destinatario dei messaggi.
- Aggiungi un breve testo introduttivo che verrà inserito prima del titolo del post nel messaggio precompilato.
- Inserisci lo shortcode `[send_whatsapp_link]` ovunque nei contenuti per mostrare il link.
- Il link generato apre una chat WhatsApp con il numero configurato e precompila il messaggio con "Testo breve + Titolo del post".

## Utilizzo

1. Installa la cartella del plugin all'interno della directory `wp-content/plugins/` del tuo sito WordPress.
2. Attiva il plugin dal pannello **Plugin** di WordPress.
3. Accedi alla voce **Send WhatsApp → Configurazione** nel menu di amministrazione e inserisci:
   - Il numero di telefono completo di prefisso internazionale (solo cifre, senza simboli o spazi).
   - Un breve testo opzionale da aggiungere prima del titolo del post nel messaggio.
4. Salva le impostazioni.
5. Aggiungi lo shortcode `[send_whatsapp_link]` dove desideri che appaia il link all'interno dei tuoi contenuti.

Il link generato rispetta il formato `https://wa.me/<numero>?text=<messaggio>`, come descritto nella documentazione ufficiale di WhatsApp.
