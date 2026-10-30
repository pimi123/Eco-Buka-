# Filtrimi i produkteve

Statusi: Backlog — zhvillimi i ardhshëm
Data: 2026-10-30

## Kërkesa

Të shtohet filtrimi i produkteve në katalog, kërkim dhe brenda kategorive/collections, me ndërfaqe të përshtatshme për mobile dhe desktop.

## Propozim për shtrirjen

- Filtra sipas kategorisë, intervalit të çmimit, disponueshmërisë dhe produkteve me zbritje.
- Kombinim filtrash, shfaqje e filtrave aktivë dhe buton për pastrimin e tyre.
- Ruajtje e filtrave në URL për ndarje të linkut dhe kthim pas në shfletues.

Detajet e filtrave të konfirmohen kur të nisë implementimi.

## Kriteret e pranimit

- Filtrat zbatohen në backend përpara pagination, mbi të gjithë rezultatin.
- Brenda kategorisë/collection shfaqen vetëm produktet e atij grupimi.
- Produktet me zbritje mbeten të parat brenda rezultateve të filtruara; rendi ekzistues ruhet brenda secilit grup.
- Ndryshimi i filtrave e kthen pagination në faqen e parë.
- Shfaqen numri i rezultateve dhe një gjendje e qartë kur nuk ka rezultate.
- Filtrimi nuk ndryshon sort_order apo të dhënat e produkteve.

## Jashtë kësaj tikete

Implementimi nuk ka nisur; kjo tiketë dokumenton punën e ardhshme.
