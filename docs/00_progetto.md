# Lead Generator — Descrizione del Progetto (High-Level)

## Visione

Creare uno strumento proprietario che analizzi aziende B2B e identifichi opportunità commerciali per i servizi Perspect e Newwave, integrando dati aziendali, segnali digitali e insight qualitativi.  
L’obiettivo è prioritizzare i prospect, qualificare le aziende con metodo e ridurre il tempo speso su lead non interessanti.

---

## Obiettivi Strategici

- Automatizzare la raccolta e valutazione delle informazioni aziendali.
- Qualificare i lead in modo oggettivo e scalabile, riducendo bias e intuizioni non supportate dai dati.
- Identificare rapidamente punti di ingresso commerciali.
- Integrare analisi tecniche, branding, digital e strategiche in un unico workflow.
- Fornire output strutturati utili sia per il team commerciale che per la creazione di materiali di outreach.

---

## Struttura del Repository di Progetto

Per garantire coerenza e allineamento, tutta la documentazione e gli asset di configurazione vivono nella cartella `docs/` e devono essere considerati **la bibbia del progetto**.

- `docs/*.md` — documentazione funzionale e tecnica (vision, data model, workflow operativi).
- `docs/json/` — asset JSON di riferimento:
  - configurazione ACF di WordPress (field group, mapping campi);
  - workflow n8n ufficiali usati per enrichment, parsing, upsert e logging.

Ogni modifica ai processi o alle integrazioni deve aggiornare questi file, che rappresentano l'unica fonte di verità condivisa.

---

## Architettura Concettuale

### Componenti principali

- **Backend & Database:** WordPress tailor-made (CPT “Aziende”, campi ACF avanzati, dashboard analitica).
- **Workflow Automation:** n8n self-hosted (Company enrichment, analisi, orchestrazione agenti AI).
- **Frontend:** interfaccia web interna per consultare risultati, scorecards, insight e raccomandazioni.

### Origine Dati

- Informazioni aziendali di input minime (ragione sociale, dominio, P.IVA).
- Enrichment automatico tramite fonti pubbliche + inference AI.
- Analisi semantica sito web + presenza digitale.
- Dati economici e di settore (ove disponibili).

---

## Moduli Iniziali

### 1) Company Enrichment

**Scopo:** trasformare dati grezzi minimi in un profilo aziendale arricchito e valutabile.

Contenuti principali:

- Dati anagrafici e strutturali
- Classificazione settore/attività
- Dimensione aziendale e indicatori economici essenziali
- Mappatura prodotti/servizi
- Analisi presenza digitale iniziale
- Verifica target vs non-target (fit strategico Perspect/Newwave)

**Output:**

- Profondità minima per decidere se procedere
- Score preliminare (es. Strategico / Interessante / Non prioritario)

### 2) Analisi Verticali (executed only if in-target)

Ogni analisi approfondisce un ambito rilevante per value proposition Perspect/Newwave:

- Identità & Branding
- Website & UX
- SEO & Content
- Digital Marketing
- Tecnologia & Performance web
- ESG & comunicazione sostenibilità
- Strategia digitale complessiva
- Competitor & positioning

**Output:** insight strutturati + raccomandazioni operative.

---

## Sales Intelligence Agent

Unifica i dati provenienti da enrichment e moduli verticali per produrre:

- Sintesi strategica
- Pains, Gaps & Opportunities
- Priorità di intervento
- Proposta di approccio commerciale
- Possibili pacchetti Perspect/Newwave
- To-do operativi per la pipeline vendite

---

## Flusso Operativo (alto livello)

1. Inserimento dati base azienda
2. Company enrichment
3. Decisione automatica “go/no go”
4. Esecuzione analisi verticali (per aziende qualificate)
5. Unificazione insight via Sales Agent
6. Output finale consultabile nel portale:
   - Profilo completo
   - Scorecard
   - Report sintetico e dettagliato
   - Raccomandazioni commerciali
   - Next-steps per il team sales

---

## Deliverable del Sistema

- Database strutturato delle aziende con enrichment completo
- Dashboard di qualificazione e scoring
- Report consultabili e scaricabili
- Roadmap commerciale per ogni lead

---

## Metriche di Successo

- Riduzione tempo di qualifica lead
- Aumento tasso conversione lead → opportunità
- Standardizzazione della qualità delle valutazioni
- Pipeline commerciale più pulita e prioritaria
- Insight data-driven per comunicazione e vendita

---

## Espansioni Future (non vincolanti)

- Modulo di outbound automatizzato
- Integrazione CRM
- Lead scoring dinamico continuo
- Benchmarking competitivo cross-dataset
- Modulo predittivo su probabilità di chiusura

---

## Nota finale

L’obiettivo non è sostituire l’intuizione commerciale, ma **amplificarla con metodo, dati e struttura**, rendendo più scalabile e scientifico il processo di generazione opportunità per Perspect e Newwave.
