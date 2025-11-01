# Data Model & Company Enrichment — Perspect Lead Intelligence

Questo documento definisce il modello dati aziendale e descrive il workflow di Company Enrichment per la piattaforma di lead intelligence e prospect scoring di Perspect / Newwave.

## Obiettivo

Archiviare e normalizzare in modo coerente e scalabile i dati raccolti tramite il processo:

Company Enrichment → Revisione → Sintesi → Parsing → Upsert WP → Analisi verticali

Rendere il dataset utilizzabile per:

- Prioritizzazione commerciale
- Segmentazione target
- AI-assisted account planning
- Benchmarking e dashboard

## Struttura Top-Level

### Campo: company_name_full

- Tipo: string
- Note: Ragione sociale

### Campo: partita_iva

- Tipo: string
- Note: 11 cifre (senza IT)

### Campo: domain

- Tipo: string
- Note: dominio.tld

### Campo: address

- Tipo: string
- Note: via + numero

### Campo: city

- Tipo: string
- Note: città

### Campo: province

- Tipo: string
- Note: es. VE

### Campo: phone

- Tipo: string
- Note: preferibile fisso aziendale

### Campo: email

- Tipo: string
- Note: email istituzionale

### Campo: linkedin_url

- Tipo: string
- Note: pagina aziendale

## Company Profile

### Campo: sector_specific

- Tipo: string
- Note: es. “Produzione valvole industriali”

### Campo: business_type

- Tipo: string
- Note: Industriale / Servizi professionali / Tech / Retail / ecc.

### Campo: employee_count

- Tipo: string o int
- Note: range o numero

### Campo: growth_stage

- Tipo: enum
- Valori: Startup / Crescita / Consolidata / Corporate / Non definito

### Campo: geography_scope

- Tipo: enum
- Valori: Locale / Regionale / Nazionale / Internazionale

### Campo: short_bio

- Tipo: string
- Note: Breve descrizione o analisi dell’azienda

## Economia e Budget

### Campo: annual_revenue

- Tipo: string
- Note: €x–y o cifra singola

### Campo: ebitda_margin_est

- Tipo: number
- Note: percentuale stimata da benchmark di settore o RPE

### Campo: marketing_budget_est

- Tipo: string
- Note: €x–y o “Non reperito”

### Campo: budget_tier

- Tipo: enum
- Valori: 0 micro / 1 small / 2 medium / 3 large / 4 enterprise
- Nota: l’API `wp/v2` accetta etichette testuali (Micro, Small, Medium, Large, Enterprise). Se il campo ACF è configurato con valori numerici 0–4, il Parsing Node converte le etichette nell’indice numerico prima dell’upsert.

### Campo: financial_confidence

- Tipo: number
- Range: 0–100
- Note: livello di confidenza sul dato

#### Regola EBITDA stimato

- più di 20 dipendenti → 10–18%
- consulting/professional → 15–35%
- manufacturing → 8–15%

#### Derivazione budget marketing

- Startup → 2–5 % del fatturato
- Crescita → 1.5–3 %
- Consolidata → 0.8–1.2 %
- Corporate → 0.5–1 %

## Digital e Media

### Campo: social_links

- Tipo: textarea
- Note: una URL per riga
- linkedin_url è un campo separato; `social_links` accetta una URL per riga. Il parser deduplica automaticamente ed estrae la LinkedIn Company Page se presente.

### Campo: digital_maturity_score

- Tipo: number
- Range: 0–100
- Note: calcolato con rubrica a punti

#### Rubrica digital maturity

- +15 sito aggiornato <12 mesi
- +10 SEO on-page coerente
- +10 form/CTA funzionanti
- +10 blog o risorse attive
- +10 newsletter o automation
- +15 ADV attiva visibile
- +10 tracking (GA / Tag Manager)
- +10 video o case study
- +10 almeno 3 social aggiornati <60 giorni

## Fit Perspect / Newwave

### Campo: qualification_status

- Tipo: enum
- Valori: da valutare / rifiutata / qualificata

### Campo: qualification_reason

- Tipo: string
- Note: motivazione breve

### Campo: service_fit

- Tipo: string
- Note: servizi più pertinenti

### Campo: priority_score

- Tipo: number
- Range: 0–100
- Note: punteggio per sorting CRM

#### Filosofia del data model

- Solo campi utili alla qualificazione commerciale
- Evitare dati burocratici non rilevanti
- Scalabile per futuri agenti verticali (SEO, social, branding)
- Ogni valore deve avere fonte e livello di confidenza
- Progettato per account-based marketing e ranking

#### Future Fields (fase 2)

- lead_score_ml
- deal_history
- content_topics_cluster
- buyer_persona_match

## Meta Enrichment & Telemetria

### Campo: enrichment_last_status_code

- Tipo: number
- Note: codice di esito (HTTP o custom) dell’ultimo workflow n8n ricevuto da WordPress.

### Campo: enrichment_last_message

- Tipo: string
- Note: descrizione testuale dell’ultimo esito (log operativi / messaggi errore).

### Campo: enrichment_last_at

- Tipo: datetime string
- Note: timestamp ISO 8601 dell’ultimo upsert completato. Alias legacy `data_ultimo_enrichment` accettato in sola lettura; standard = `enrichment_last_at`.

### Campo: enrichment_sources

- Tipo: string (JSON o testo)
- Note: elenco fonti utilizzate dall’agente (popolato da n8n).

### Campo: enrichment_notes

- Tipo: string
- Note: note contestuali dell’arricchimento automatico.

### Campo: enrichment_citations

- Tipo: string
- Note: citazioni puntuali / URL forniti dal workflow.

### Campo: enrichment_completeness

- Tipo: number
- Note: percentuale (0–100) calcolata lato WordPress sui campi core popolati.

### Campo: enrichment_field_total

- Tipo: number
- Note: numero di campi considerati dal calcolo di completezza.

### Campo: enrichment_field_populated

- Tipo: number
- Note: numero di campi effettivamente valorizzati.

### Campo: update_count

- Tipo: number
- Note: quante volte il record è stato aggiornato dal workflow di enrichment.

### Campo: last_update_date

- Tipo: datetime string
- Note: timestamp (MySQL) dell’ultima modifica applicata dall’upsert.

Se un dato non esiste non va inventato.
Si riempie con:

- Non reperito
- Stima
- Da verificare
- E sempre un confidence score

## Company Enrichment Workflow

Architettura:
Input (nome azienda, dominio, P.IVA)
→ Agente 1 Analisi e Ricerca
→ Report testuale (1–12 sezioni)
→ Agente 2 Sintesi e Normalizzazione
→ Parsing Node (validazione schema ACF)
→ Upsert Node (REST ACF WordPress)

### Agente 1 — Analisi e Ricerca

- Scrive in italiano professionale, orientato a CEO/CMO
- Cita le fonti con livello di affidabilità
- Include benchmark di settore, segnali di budget e strumenti digitali
- Output: testo strutturato, non JSON

**Prompt utente:**
Conduci una ricerca completa e approfondita su questa azienda italiana.
Usa linguaggio business per CEO/titolari, evita tecnicismi da sviluppatore.

**Dati di partenza:**
Ragione sociale, Dominio/Sito web, P.IVA

**Obiettivi:**

- Dati anagrafici completi
- Presenza digitale e social
- Settore, prodotti, target
- Dimensione e performance
- Trust & proof
- Segnali di budget
- Opportunità per agenzia

**Istruzioni:**

- Cita sempre fonti e affidabilità
- Se dati mancanti → “Non reperito”
- Mantieni la struttura 1–12 completa
- Includi benchmark EBITDA, segnali marketing e strumenti digitali
- Output testuale narrativo, non JSON

Concludi con “Completezza informazioni: XX%” (stima motivata)

### Agente 2 — Sintesi e Normalizzazione

- Riceve il report testuale dell’agente 1
- Estrae i dati strutturati e li mappa sui campi ACF
- Applica solo inferenze plausibili e regole deterministiche
- Deriva: ebitda_margin_est, marketing_budget_est, budget_tier, digital_maturity_score
- Valida tutti i campi e genera JSON pulito

**Output JSON (schema):**

- company_name_full
- partita_iva
- address
- city
- province
- phone
- email
- domain
- linkedin_url
- social_links
- business_type
- sector_specific
- employee_count
- growth_stage
- geography_scope
- annual_revenue
- ebitda_margin_est
- marketing_budget_est
- budget_tier
- financial_confidence
- digital_maturity_score
- short_bio
- qualification_status
- qualification_reason
- service_fit
- priority_score

#### Derivazioni automatiche

- ebitda_margin_est: da benchmark o RPE
- marketing_budget_est: percentuale prudenziale del fatturato
- budget_tier: derivato dal budget marketing
- digital_maturity_score: rubrica a punti
- financial_confidence: livello di confidenza 0–100
- priority_score: sintesi opportunità 0–100

#### Criteri di qualificazione (qualification_status)

- Usa solo: da valutare, rifiutata, qualificata.
- rifiutata → dati insufficienti o contraddittori (imposta anche qualification_reason: "Dati insufficienti").
- qualificata → budget_tier ≥ Medium e carenze digitali rilevanti (motiva in qualification_reason).
- da valutare → informazioni parziali ma coerenti; profilo potenzialmente interessante ma ancora incompleto.

### Parsing Node

- Analizza e valida il JSON dell’agente 2
- Normalizza valori e tipi
- budget_tier → indice 0–4
- qualification_status coerente con: da valutare / rifiutata / qualificata; fallback: rifiutata + “Dati insufficienti”
- Deduplica social
- Trancia short_bio a 800 caratteri
- Output acfPayload pronto per upsert

### Upsert Node (WordPress REST)

**Endpoint:**
`POST https://lead.perspect.it/wp-json/wp/v2/azienda/{POST_ID}`

**Body (JSON):**
```json
{ "acf": {{ $json.acfPayload }} }
```

**Note operative:**
- Il CPT `wp/v2/azienda/{POST_ID}` accetta `acf` come contenitore dei campi ACF.
- Campi extra come `discovery_id`, `meta`, `workflow_status` richiedono endpoint custom o meta registrati via tema/plugin.

**Authentication:** stessa del nodo principale (Basic Auth o App Password)

**Status code:**

- 200 OK
- 400 valori non validi
- 403 permessi mancanti
- 404 post non trovato

### Log Enrichment Result WP

Scopo: scrivere su WordPress lo stato dell’upsert (HTTP status, messaggio, timestamp).

**Endpoint:**
`POST https://lead.perspect.it/wp-json/wp/v2/azienda/{{ $json.upsert_raw.id }}`

**Headers:**
`Content-Type: application/json`

**Body (JSON):**
```json
{
  "acf": {
    "enrichment_last_status_code": {{ $json.upsert_status_code }},
    "enrichment_last_message": "{{ $json.upsert_message }}",
    "enrichment_last_at": "{{ $json.upsert_at_iso }}"
  }
}
```

**Prerequisito:**
I tre campi ACF (enrichment_last_status_code, enrichment_last_message, enrichment_last_at) devono esistere e appartenere al Field Group del CPT azienda.

### Filosofia generale

- Nessuna invenzione o stima arbitraria
- Ogni campo tracciabile a fonte o logica derivativa
- Pipeline pensata per audit e riaddestramento automatico
- Compatibilità piena con ACF e WordPress REST API

## Versione

Company Enrichment v1.0 – Novembre 2025  
Parte del progetto Lead Generator / Data Enrichment Suite  
Autore: GPT-5 + n8n / WordPress ACF

## Webhook & Respond

- Nel nodo Webhook impostare `Respond = Using Respond to Webhook node`.
- Nel nodo Respond to Webhook restituire:
  ```json
  { "ok": {{ $json.upsert_ok }}, "status": {{ $json.upsert_status_code }}, "message": "{{ $json.upsert_message }}" }
  ```
- Impostare `Response Code = {{ $json.upsert_status_code || 200 }}`.
- Per il Test URL eseguire il workflow in modalità “Execute workflow” prima di chiamarlo esternamente.
