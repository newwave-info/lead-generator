# Web Analysis — Workflow & Data Contract

Il workflow **Lead Generator | Web Development** esegue un’analisi qualitativa e strategica del sito web aziendale, produce un report revisionato e lo salva su WordPress come post di tipo `analisi`. Il focus è su posizionamento, messaggi chiave, branding, trust, funnel di conversione, contenuti e opportunità per agenzie di comunicazione e marketing B2B.

## Descrizione Generale

- Analisi automatica del dominio aziendale collegato alla scheda.
- Output strutturati per strategia, revisione consulenziale e sintesi dati.
- Pubblicazione o aggiornamento dell’analisi su WordPress, collegandola all’azienda madre.

## Scopo

Produrre in modo continuativo:

- Analisi strategica completa (testo “Perplexity deep research”).
- Revisione consulenziale senior (“Analisi migliorata”).
- Sintesi strutturata in JSON con insight e metriche chiave.
- Upsert del post `analisi` collegato all’azienda, con tracciamento dello stato.

## Workflow Step-by-Step

### 1. Trigger — Webhook

- **Tipo:** Webhook (`POST`)
- **Percorso:** `/web-development-agent`
- **Input obbligatorio:** `company_id` (ID WordPress dell’azienda)
- Attiva l’intera pipeline di analisi a partire dai dati aziendali correnti.

### 2. Read Azienda WP

- **Nodo:** `Read Azienda WP`
- **Endpoint:** `https://lead.perspect.it/wp-json/wp/v2/azienda/{{ company_id }}`
- **Autenticazione:** `Lead 18kPRo WP` (HTTP Basic Auth)
- Recupera il payload ACF completo dell’azienda (ragione sociale, dominio, settore, descrizione, ecc.) da passare agli agenti successivi.

### 3. Agente di analisi

- **Modello:** GPT-5 Search API
- **Ruolo:** consulente strategico senior (brand, comunicazione, UX)
- Genera un testo narrativo in italiano rivolto a CEO/CMO, organizzato in sezioni:
  - Sintesi esecutiva
  - Brand e posizionamento
  - Credibilità e trust
  - Funnel & conversione
  - Contenuti
  - SEO
  - UX & UI
  - Rischi e red flag
  - Opportunità e raccomandazioni
  - Domande intelligenti al cliente

### 4. Agente di revisione

- **Modello:** GPT-5
- **Ruolo:** revisore strategico senior
- Migliora il testo generato eliminando ridondanze, rafforzando tono e chiarezza, aggiungendo insight e garantendo coerenza narrativa.
- Output finale: **“Analisi migliorata”** con chiusura obbligatoria che include:
  - Domande critiche da porre al prospect (max 6)
  - Idee di valore da proporre come agenzia (max 5)

### 5. Agente di sintesi

- **Modello:** GPT-5 Mini
- **Ruolo:** analista senior di estrazione strutturata
- Riceve l’analisi revisionata e restituisce **solo** un JSON con schema:

```json
{
  "riassunto": "string (≤280 caratteri)",
  "punti_debolezza": ["..."],
  "punti_forza": ["..."],
  "opportunita": ["..."],
  "azioni_rapide": ["..."],
  "voto_qualita_analisi": 0,
  "voto_qualita_dati": 0,
  "numeri_chiave": {
    "organic_ctr": {
      "label": "CTR organico",
      "dato": 2.4,
      "fonte": "stima"
    }
  }
}
```

- Regole:
  - Liste da 3 a 8 voci, ordinate per impatto e senza duplicati.
  - Azioni rapide operative, completabili in ≤2 settimane.
  - Numeri chiave opzionali; ogni metrica deve avere `label`, `dato`, `fonte`.
  - I voti valutano qualità dell’analisi e dei dati di partenza (scala 0–100).

### 6. Parsing — Code Node

- Valida il JSON prodotto dall’agente di sintesi.
- Normalizza i campi testuali e converte le liste in array coerenti.
- Calcola i conteggi (`numero_*`) per punti di forza, debolezza, opportunità e azioni rapide.
- Converte i voti in interi 0–100 e li salva nei campi ACF dedicati.
- Costruisce `json_dati_strutturati` serializzando il blocco `numeri_chiave` con schema `{ label, dato, fonte }` per ogni KPI.
- Mantiene un backup dell’output originale degli agenti per audit (analisi iniziale e revisione).

### 7. Upsert WordPress

- **Nodo:** `Upsert WP`
- **Metodo:** `POST` su `https://lead.perspect.it/wp-json/wp/v2/analisi` (aggiornamento tramite ID quando esiste).
- **Campi principali scritti:**
  - `title`: `{{ company_name }} | Web Analysis`
  - `status`: `publish`
  - `agent_type`: `7` (Web Development)
  - **ACF aggiornati:**
    - `parent_company_id`
    - `analisy_perplexity_deep_research`
    - `riassunto`
    - `punti_di_forza`, `punti_di_debolezza`, `opportunita`, `azioni_rapide`
    - `numero_punti_di_forza`, `numero_punti_di_debolezza`, `numero_opportunita`, `numero_azioni_rapide`
    - `voto_qualita_analisi`, `qualita_dati`
    - `json_dati_strutturati` (JSON dei numeri chiave con `{label, dato, fonte}`)
    - `revisione_analisi_completa`

### 8. Logging

- **Compose Upsert Result:** calcola `upsert_ok`, `upsert_status_code`, `upsert_message`, `analysis_id`, `analysis_link`, `upsert_at_iso`.
- **Log Upsert Result WP:** `POST https://lead.perspect.it/wp-json/wp/v2/azienda/{company_id}` aggiornando:
  - `analysis_last_status_code`
  - `analysis_last_message`
  - `analysis_last_at`

### 9. Webhook Response

- **Nodo:** `Return Status`
- Risposta JSON al chiamante:

```json
{
  "ok": true,
  "status": 200,
  "message": "OK"
}
```

- `Response Code`: `{{ upsert_status_code || 200 }}`

## Mappatura Campi ACF (Post type: analisi)

### Campo: parent_company_id

- **Tipo:** `post_object` (azienda)
- **Note:** collega l’analisi alla scheda aziendale originale.

### Campo: analisy_perplexity_deep_research

- **Tipo:** `wysiwyg`
- **Note:** testo completo generato dall’agente di analisi (versione “base”).

### Campo: riassunto

- **Tipo:** `text`
- **Note:** sintesi ≤280 caratteri prodotta dall’agente di sintesi.

### Campo: punti_di_forza

- **Tipo:** `text`
- **Note:** elenco normalizzato; il front-end gestisce la visualizzazione puntuale.

### Campo: punti_di_debolezza

- **Tipo:** `text`
- **Note:** lista delle criticità prioritarie.

### Campo: opportunita

- **Tipo:** `text`
- **Note:** opportunità strategiche e competitive.

### Campo: azioni_rapide

- **Tipo:** `text`
- **Note:** task operativi ≤2 settimane per ingaggi commerciali rapidi.

### Campo: numero_punti_di_forza

- **Tipo:** `number`
- **Note:** conteggio generato dal parsing; usato per widget e indicatori.

### Campo: numero_punti_di_debolezza

- **Tipo:** `number`
- **Note:** numero di criticità individuate.

### Campo: numero_opportunita

- **Tipo:** `number`
- **Note:** totale delle opportunità identificate.

### Campo: numero_azioni_rapide

- **Tipo:** `number`
- **Note:** quantità di task rapidi proposti.

### Campo: voto_qualita_analisi

- **Tipo:** `number`
- **Note:** punteggio 0–100 calcolato dall’agente di sintesi sulla qualità dell’elaborato finale.

### Campo: qualita_dati

- **Tipo:** `text`
- **Note:** scala 0–100 (coerente con `voto_qualita_dati`) salvata come stringa per compatibilità front-end.

### Campo: json_dati_strutturati

- **Tipo:** `textarea`
- **Note:** JSON serializzato dei KPI chiave con struttura `{ "chiave": { "label", "dato", "fonte" } }`, usato per widget analitici.

### Campo: revisione_analisi_completa

- **Tipo:** `textarea`
- **Note:** testo finale revisionato (“Analisi migliorata”) pronto per la scheda azienda.

### Campo: analysis_last_status_code

- **Tipo:** `text`
- **Note:** esito HTTP dell’ultimo upsert (logging lato azienda).

### Campo: analysis_last_message

- **Tipo:** `textarea`
- **Note:** messaggio di log o errore dell’ultimo upsert.

### Campo: analysis_last_at

- **Tipo:** `text`
- **Note:** timestamp ISO 8601 dell’ultimo aggiornamento completato.

## Flusso Logico

`Webhook → Lettura azienda → Agente di analisi → Agente di revisione → Agente di sintesi → Parsing → Upsert WP → Logging → Return Status`

## Output WordPress

- **Post type:** `analisi`
- **Tassonomia:** `agent_type = 7 (Web Development)`
- **Slug:** auto-generato (`{azienda}-web-analysis`)
- **Relazione:** `acf[parent_company_id]` collega all’azienda analizzata
- **Status:** `publish`

## Note Operative

- Tutti i modelli OpenAI utilizzano la credenziale `OpenAi account`.
- Timeout HTTP globale: 30 secondi.
- L’array `numeri_chiave` deve sempre rispettare la struttura `{ label, dato, fonte }`; in assenza di metriche affidabili salvare `{}`.
- I backup degli output (analisi iniziale e revisione) restano disponibili per audit e per eventuale riutilizzo in altri moduli (Company Enrichment, Lead Qualification).
