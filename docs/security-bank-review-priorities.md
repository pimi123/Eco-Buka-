# Eco Buka - Prioritetet Para Launch Dhe Bank Review

Date: 2026-08-13

## Qellimi

Ky dokument permbledh prioritetet kryesore qe duhet te trajtohen perpara se Eco Buka te konsiderohet website i sigurt, i besueshem dhe i pergatitur per rishikim nga banka ose payment provider.

Fokusi nuk eshte ende implementimi i pagesave online. Fokusi eshte qe website te kete baze te mire sigurie, besueshmerie, dokumentimi dhe operimi.

## Prioritetet Kryesore

### 1. Company Information Page

Duhet te krijohet nje faqe ose seksion zyrtar me informacionet e kompanise.

Duhet te perfshije:

- Emri ligjor i biznesit
- Emri tregtar / brendi
- Numri i regjistrimit te biznesit
- Numri fiskal / TVSH, nese aplikohet
- Adresa zyrtare
- Adresa operative, nese eshte ndryshe
- Email zyrtar
- Numri i telefonit
- Orari i punes
- Zona e sherbimit
- Deklarata per distributor / partner shitjeje
- Kanalet e mbeshtetjes

Pse eshte e rendesishme:

- Rrit besueshmerine per klientet.
- Ndihmon ne rishikim nga banka.
- E ben biznesin me transparent dhe profesional.

### 2. Legal Dhe Policy Pages

Duhet te shtohen faqet ligjore dhe politikat baze.

Faqet e nevojshme:

- Terms and Conditions
- Privacy Policy
- Cookie Policy, nese perdoren cookies jo te domosdoshme
- Delivery Policy
- Return and Refund Policy
- Cancellation Policy
- Warranty and Complaints Policy
- Payment Methods and Payment Security notice

Pse eshte e rendesishme:

- Klienti duhet ta dije qarte si funksionon porosia, dorezimi, kthimi, anulimi dhe garancioni.
- Banka/payment provider zakonisht kontrollon keto faqe para aprovimit.
- E ul riskun ligjor dhe rrit transparencen.

### 3. HTTPS Dhe Security Headers

Website dhe API duhet te perdorin HTTPS ne production.

Duhet te kontrollohen:

- SSL/TLS certifikate aktive
- Ridrejtim nga HTTP ne HTTPS
- TLS modern
- HSTS
- `X-Content-Type-Options`
- `X-Frame-Options` ose `frame-ancestors`
- `Referrer-Policy`
- `Permissions-Policy`
- CORS vetem per domainet e sakta

Pse eshte e rendesishme:

- Mbron trafikun mes klientit dhe serverit.
- Jep sinjal besueshmerie per banka dhe klientet.
- Redukton rreziqe si clickjacking, MIME sniffing dhe keqperdorim te API.

### 4. Admin Security

Admin paneli duhet te forcohet para perdorimit serioz ne production.

Duhet te kontrollohen:

- Admin password te forte
- Llogari individuale per cdo admin
- MFA/TOTP, nese vendoset si faze sigurie
- Rate limit per login
- Session security
- Audit log per ndryshime te rendesishme
- Kufizim aksesi per admin routes

Pse eshte e rendesishme:

- Admin paneli kontrollon produktet, cmimet, porosite dhe imazhet.
- Nese admin komprometohet, website dhe porosite mund te manipulohen.

### 5. Upload Security

Upload-et duhet te jene te sigurta dhe te kontrolluara.

Duhet te kontrollohen:

- Lejohen vetem file types te nevojshme
- Validim MIME/content, jo vetem extension
- Limit per madhesi file
- Limit per dimensione/pixel count te imazheve
- Emra random nga serveri
- Moslejim i file executable
- Kujdes me SVG, PDF dhe video
- Fshirje e sigurt vetem kur file nuk perdoret diku tjeter
- Storage te mos ekzekutoje kode

Pse eshte e rendesishme:

- Upload-et jane nje prej pikave me te rrezikshme ne CMS.
- Mbron serverin nga file keqdashes.

### 6. Server Backups

Duhet te kete plan backup per database dhe uploaded media.

Duhet te perfshije:

- Backup automatik i databazes
- Backup i file uploads
- Kopje off-server/off-account
- Retention policy
- Test restore
- Dokumentim i procesit

Pse eshte e rendesishme:

- Mbron biznesin nga humbja e te dhenave.
- Rrit besueshmerine operacionale.
- Eshte pike e rendesishme per production readiness.

### 7. Basic Monitoring

Duhet te kete monitorim baze per serverin dhe aplikacionin.

Duhet te monitorohen:

- Uptime i website
- Uptime i API
- HTTPS certificate expiry
- HTTP 500 errors
- Disk usage
- CPU/RAM
- Backup success/failure
- Laravel errors
- Order/API failures

Pse eshte e rendesishme:

- Problemet duhet te zbulohen shpejt.
- Pa monitoring, nje gabim ne checkout/API mund te mbetet pa u pare.

### 8. Input Validation Review

Te gjitha format dhe API endpoints duhet te validojne inputin ne server.

Duhet te kontrollohen:

- Checkout form
- Contact form
- Admin forms
- Product/category forms
- Order creation API
- Search endpoint
- File upload fields

Pse eshte e rendesishme:

- Nuk duhet t'i besohet browserit.
- Cmimi, shuma, statusi, roli admin dhe te dhenat komerciale duhet te jene server-authoritative.

### 9. Remove Test/Demo Production Content

Para launch duhet te pastrohen ose te caktivizohen te dhenat testuese.

Duhet te kontrollohen:

- Produkte test
- Kategori test
- Imazhe placeholder
- Tekste demo
- Cmimet jo reale
- Links qe nuk jane final
- Fallback/demo data ne production

Pse eshte e rendesishme:

- Test content ul besueshmerine.
- Mund te ngaterroje klientet.
- Mund te shfaqe cmime apo produkte jo reale.

### 10. Security Documentation For Bank Review

Duhet te pergatitet dokumentim i thjeshte per sigurine dhe funksionimin e website.

Duhet te perfshije:

- Si hostohet website
- Si hostohet API/backend
- Si mbrohet database
- Si funksionojne uploads
- Si validohen porosite
- Si mbrohet admin panel
- Si funksionon HTTPS
- Cilat te dhena ruhen
- Deklarate qe nuk ruhen te dhena te kartelave
- Plan backup dhe monitoring

Pse eshte e rendesishme:

- Banka/payment provider mund te kerkoje evidence.
- Ndihmon klientin te kuptoje cfare eshte gati dhe cfare mungon.
- E ben projektin me profesional.

## Renditja E Rekomanduar E Punes

1. Company Information page
2. Legal dhe policy pages
3. HTTPS dhe security headers
4. Admin security
5. Upload security
6. Server backups
7. Basic monitoring
8. Input validation review
9. Remove test/demo production content
10. Security documentation for bank review

## Cfare Nuk Fillojme Ende

Per momentin nuk fillojme:

- Payment gateway
- Callback/webhook payments
- Refund module
- PCI package
- Bank sandbox integration
- Live payment button

Keto vijne me vone, pasi website te jete i sigurt dhe i pergatitur mire per production.

## Konkluzion

Prioriteti kryesor per Eco Buka tani eshte te behet website i besueshem, i sigurt, i dokumentuar dhe i gatshem per launch. Vetem pas kesaj faze ka kuptim te fillohet me pagesa online dhe integrim me banke/payment provider.
