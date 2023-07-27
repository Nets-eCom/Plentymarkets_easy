# Nexi | Nets Checkout
## Online-Zahlungen. Lokal. Relevant.

**Der ultimative plentymarket Checkout mit dem persönlichen und lokalen Support!** Unser Checkout und Payment-Mix für mehr Umsatz. Vermeiden Sie Kaufabbrüche indem Sie die bevorzugten Zahlungsarten Ihrer Kunden anbieten. Unser Checkout hat für Sie und Ihre Kunden alles an Bord:

- Kreditkarte
- PayPal und Apple Pay
- Sofortüberweisung
- Lastschrift
- AMEX
- Kauf auf Rechnung
- Ratenzahlung

**Auf Konvertierung optimiert.** Mit folgenden Features und unseren Leistungen unterstützen wir Sie, Ihre Umsatzziele und Ihr Wachstum zu erreichen: **Profitieren Sie von unschlagbaren Konditionen und weiteren Vorteilen:**

- Keine Einrichtungs- und monatliche Grundgebühren
- Alles im Blick in unserem Dashboard
- Alle wichtigen Bezahlarten
- Verkaufen auch im Ausland
- Quick Checkout
- Checkout Styler
- Ein Vertrag. Ein Ansprechpartner

**Schnelles Einrichten.** Einfaches Onboarding mit unserem lokalen Support, der jederzeit zur Seite steht.

Für die Nutzung des Checkouts benötigen Sie einen Account und die Zugangsdaten von Nexi I Nets. [Registrieren Sie sich jetzt, um den Checkout zu nutzen](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

**Nets ist Teil der Nexi Gruppe** und mit weltweit 6,1 Billionen Transaktionen jährlich Europas größter PayTech Anbieter. Unsere Payment- und Checkout-Lösungen sind bei mehr als 170.000 Händlern im Einsatz! Wir bieten Ihnen das ultimative Checkout Plug-in für alle Ihre E-Commerce-Transaktionen, so dass Sie sich besser auf Ihr Geschäft und Ihre Kunden konzentrieren können. Mit unserer Lösung können Sie Ihre Konversionsrate optimieren, die Zahl der Kaufabbrüche reduzieren und die Kundenbindung erhöhen. **Erleben Sie die Vorteile unserer Checkout-Lösung und heben Sie Ihren plentymarket Webshop auf das nächste Level.**

[Registrieren Sie sich jetzt, um den Checkout zu nutzen](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)
## Nexi | Nets Checkout - Integrationsleitfaden
### Konfigurationsanleitung - Assistent
1. Nach der Installation des Plug-ins steht dir der Nets Easy Plugin Assistent zur Verfügung, welcher durchlaufen werden muss. Du findest diesen unter **”Einrichtung » Assistenten » Plugins » {Plugin-Set} » Nets Easy”**

![NE_Assistant](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_assistent.png)

2. Hinterlege entsprechend im ersten Schritt des Assistenten (Zugangsdaten) deinen Checkout Key und deinen Secret Key, diese findest du in deinem Nets Easy Dashboard unter **“Unternehmen » Integration”** 
(Bei Fragen zu deinem Nets Easy Account wende dich bitte direkt an den [Support](https://developers.nets.eu/nets-easy/en-EU/support/))

![NE_Dashboard](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_dashboard.png)

3. Die Schritte 2 (Verfügbarkeit) und 3 (Zusatzinformationen) des Assistenten sind Standardkonfigurationen einer Zahlart im plentymarkets Kontext.

4. Im 4. Schritt des Assistenten (Logo) kannst du sowohl das Logo der Zahlart selbst anpassen, als auch aus einer festen Anzahl zusätzlicher Zahlungsarten-Icons wählen, um diese an der Zahlart im Checkout anzeigen zu lassen. Damit kannst du deinen Kunden klar und deutlich kommunizieren, welche Zahlarten über Nets Easy verfügbar sind.

![NE_frontend_icons](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_icons_frontend.png)

5. Im 5. und letzten Schritt des Assistenten (Zusätzliche Einstellungen) findest du spezifische Einstellungen, welche sich auf die Plugin-Funktionalitäten auswirken: 
   - “**Konsumentendaten außerhalb des Checkouts**” - Wenn du diese Funktion aktivierst, dann werden Kundeninformationen aus der Rechnungsadresse automatisch an den Net Easy Checkout übermittelt. Dein Kunde muss seine Daten also nicht doppelt eintragen. 
   - “**Backend Benachrichtigungen**” - Wenn du diese Funktion aktivierst, dann wirst du über Fehler, welche in den Ereignisaktionen des Plugins auftreten können, mittels Benachrichtigung im Backend informiert.

6. Fertig - Die Konfiguration des Assistenten ist damit abgeschlossen. 

### Konfigurationsanleitung - Zahlungsarten in plentymarkets: 
Solltest du in deinem System mit Kundenklassen arbeiten, muss Easy Pay entsprechend für deine Kundenklasse(n) aktiviert werden, erst dann wird diese auch im Checkout sichtbar.

1. Die Konfiguration deiner Kundenklasse(n) findest du unter: **”Einrichtung » CRM » Kundenklassen”** 

2. Im Bereich “Erlaubte Zahlungsarten” muss dann entsprechend Easy Pay hinzugefügt werden

![NE_Kundenklassen](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_kundenklasse.png)

### Konfigurationsanleitung - Ereignisaktionen
Folgende Ereignisaktionen werden dir vom Plugin zur Verfügung gestellt:


1. **NetsEasy Belastung der Zahlung** - Dieses Ereignis ermöglicht es dir, eine bereits reservierte Zahlung zu belasten. 

2. **NetsEasy Stornierung der Zahlung** - Dieses Ereignis erlaubt es dir, eine reservierte Zahlung zu stornieren. 
(Bitte beachte: Eine bereits belastete Zahlung kann nicht storniert werden.)

3. **NetsEasy Rückerstattung der Zahlung** - Dieses Ereignis erlaubt es dir, eine bereits belastete Zahlung zurückzuerstatten. 
(Bitte beachte: Eine Rückerstattung kann nur basierend auf einem Auftrag von Typ Gutschrift erfolgen) 

#### Beispiel: Konfiguration der Ereignisaktionen: 
Grundsätzlich steht es dir frei, wie du die Ereignisaktionen in deinem System konfigurierst. Unsere Empfehlungen beziehen sich auf einen standardisierten Auftragsbearbeitungsprozess. 

1. **Belastung einer Zahlung - NetsEasy Belastung der Zahlung**: 
Wir empfehlen dir eine Zahlung zu belasten, sobald die entsprechenden Waren in den Versand übergeben worden sind.

   **Ereignis:** 	Warenausgang gebucht 
   **Filter:** 	    Zahlungsarten, ggf. Auftragstyp
   **Aktion:** 	    NetsEasy Belastung der Zahlung

![NE_charge_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_charge_event.png)

2. **Stornieren einer Zahlung - Stornierung der Zahlung:**
Das Stornieren einer Zahlung solltest du über deinen Stornierungsstatus abbilden und mit Hilfe von Filtern sicherstellen, dass das Ereignis nur für Aufträge ausgeführt wird, die in Verbindung zu den relevanten Zahlarten stehen.

    **Ereignis:**	Statuswechsel
    **Filter:** 	Zahlungsarten, ggf. Auftragstyp
    **Aktion:**	    Stornierung der Zahlung

![NE_cancel_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_cancel_event.png)

3. **Rückerstattung einer Zahlung - NetsEasy Rückerstattung der Zahlung:**
Die Rückerstattung einer Zahlung ist entsprechend nach Erhalt einer Retoure relevant. Das Ereignis sollte so konfiguriert werden, dass es für entsprechende Gutschriften ausgeführt werden kann. 
Wir empfehlen hier die Erstellung eines entsprechenden Status im Bereich “Gutschrift” - 11.x 

    **Ereignis:** 	Statuswechsel
    **Filter:** 	Zahlungsarten, ggf. Auftragstyp
    **Aktion:**	NetsEasy Rückerstattung der Zahlung

![NE_refund_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_refund_event.png)