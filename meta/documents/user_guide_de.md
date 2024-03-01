# Nexi Checkout
## Der ultimative Checkout 
**Online-Zahlungen. Lokal. Relevant.** Der Checkout mit dem persönlichen und lokalen Support!

- **Weniger Kaufabbrüche, Conversion optimiert und mehr Umsatz**
- **Die wichtigsten Zahlarten für Ihren Webshop und Ihre Kunden**
- **Keine Einrichtungs- und monatlichen Grundgebühren**
- **Nur für erfolgreiche Transaktionen zahlen**

**Unser Checkout und Payment-Mix für mehr Umsatz.** Lassen Sie Ihre Kunden zahlen, wie Sie es wollen! Bieten Sie die bevorzugten Zahlungsarten auch in unterschiedlichen Währungen. Unser Checkout hat alle relevanten Zahlarten an Bord:

- Gesicherte Lastschrift 
- Visa
- Mastercard
- American Express
- Maestro
- PayPal 
- Apple Pay
- Sofortüberweisung
- Lastschrift
- Kauf auf Rechnung
- Ratenzahlung

#### Profitieren Sie von unschlagbaren Konditionen und weiteren Vorteilen:
- **Keine Einrichtungs- und monatliche Grundgebühren.** Nur für getätigte Transaktionen zahlen.
- **Alles im Blick im Dashboard.** Haben Sie die volle Transparenz und Kontrolle. Alle Transaktionen, Gebühren und Auszahlungen sind jederzeit einsehbar. 
- **Alle wichtigen Bezahlarten und unterschiedliche Währungen.** Ihre Kunden wollen in Ihrem Webshop zahlen, wie es ihnen gefällt.
- **Quick Checkout.** Mit der Remember-me Funktion wird der Bestellprozess verkürzt und die Conversion gesteigert.
- **Checkout Styler.** Anpassbar auf das Look & Feel des Webshops für eine optimale User Experience.
- **Schnelles Einrichten.** Einfaches Onboarding mit unserem lokalen Support, der jederzeit zur Seite steht.

#### Sicherheit ist für uns wichtig!
Wir sind ein PCI-DSS Level 1 zertifiziertes Unternehmen und erfüllen die neuesten PSD2 und Strong Customer Authentication Anforderungen. Die Zahlungsdaten werden nur in unserer sicheren Umgebung verarbeitet.

**Unser Checkout ist auf Konvertierung optimiert:** Mit unserem Checkout verbessern Sie Ihre Konversionsrate, reduzieren Sie Kaufabbrüche, steigern die Kundenbindung und erzielen im Ergebnis einfach mehr Umsatz. 

**Erleben Sie die Vorteile unserer Checkout-Lösung und heben Sie Ihren Webshop auf das nächste Level.**

**Ihre Vorteile**
- Die wichtigsten Zahlarten in Ihrem Webshop
- Keine Einrichtungs- und monatliche Grundgebühren
- Nur für erfolgreiche Transaktionen zahlen
- Optimale User Experience: Checkout ist an das Webshop Design anpassbar
- Optimiert auf Konvertierung für mehr Umsatz
- Lokaler und persönlicher Support
- Mehr als 140.000 Onlinehändler vertrauen uns

Für die Nutzung des Checkouts benötigen Sie einen Account und die Zugangsdaten von Nexi. [Registrieren Sie sich jetzt, um den Checkout zu nutzen](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

Die Nexi Gruppe ist mit weltweit 6,1 Billionen Transaktionen jährlich Europas führender PayTech Anbieter. Unsere Payment- und Checkout-Lösungen sind bei mehr als 140.000 Händlern im Einsatz! Wir kennen den Onlinehandel sowie die lokalen Märkte, wie unsere Westentasche. Unser Checkout wurde für den lokalen E-Commerce von Experten in Ihrer Nähe entwickelt. Mit unserem lokalen Fokus bieten wir Know-how, Unterstützung und Marktdaten, um Händlern Zahlungslösungen zur Verfügung zu stellen, mit denen sie erfolgreich und reibungslos in ihren Märkten wachsen. **Erleben Sie die Vorteile unserer Checkout-Lösung und heben Sie Ihren plentymarket Webshop auf das nächste Level.**

[Registrieren Sie sich jetzt, um den Checkout zu nutzen](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)
## Nexi Checkout - Integrationsleitfaden
### Konfigurationsanleitung - Assistent
1. Nach der Installation des Plug-ins steht dir der Nexi Checkout Plugin Assistent zur Verfügung, welcher durchlaufen werden muss. Du findest diesen unter **”Einrichtung » Assistenten » Plugins » {Plugin-Set} » Nexi Checkout”**

![NE_Assistant](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_assistent.png)

2. Hinterlege entsprechend im ersten Schritt des Assistenten (Zugangsdaten) deinen Checkout Key und deinen Secret Key, diese findest du in deinem Nexi Dashboard unter **“Unternehmen » Integration”** 
(Bei Fragen zu deinem Nexi Account wende dich bitte direkt an den [Support](https://developers.nets.eu/nets-easy/en-EU/support/))

![NE_Dashboard](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_dashboard.png)

3. Im 2. Schritt des Assistenten (Verfügbarkeit) kannst du auswählen welche Zahlungsarten du in deinem Checkout anbieten möchstest.

![NE_assistant_verfuegbarkeit](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_assistent_verfuegbarkeit.png)

4. Im 4. Schritt des Assistenten (Zusätzliche Einstellungen) findest du spezifische Einstellungen, welche sich auf die Plugin-Funktionalitäten auswirken: 
   - “**Konsumentendaten außerhalb des Checkouts**” - Wenn du diese Funktion aktivierst, dann werden Kundeninformationen aus der Rechnungsadresse automatisch an den Nexi Checkout übermittelt. Dein Kunde muss seine Daten also nicht doppelt eintragen. 
   - “**Backend Benachrichtigungen**” - Wenn du diese Funktion aktivierst, dann wirst du über Fehler, welche in den Ereignisaktionen des Plugins auftreten können, mittels Benachrichtigung im Backend informiert.

5. Im 5. Schritt des Assistenten (Webhook Einstellungen) kannst du zusätzliche Webhook-Funktionalitäten aktivieren und konfigurieren:
    - "**Auftragsstatus bei *X* ändern**" - Es ist möglich den Status einer Bestellung basierend auf einem bestimmten Webhook-Event zu verändern. (z.B. setze den Auftragsstatus zu 3.X, wenn die Belastung einer Zahlung nicht möglich gewesen ist)
    - "**Gutschrift in plentymarkets bei Rückerstattung im Dashboard erzeugen**" - Es ist möglich eine Gutschrift, welche im Nexi Dashboard erstellt worden ist, automatisiert bei plentymarkets erstellen zu lassen. Zusätzlich kann auch hier konfiguriert werden in welchem Status diese erstellt werden soll. 

6. Im 6. Schritt des Assistenten (Apple Pay) kannst du den Inhalt einer Domain Verifikations Datei hinterlegen. Dies ist notwendig, wenn du Apple Pay in deinem Checkout anbieten möchtest, ansonsten ist dieser Schritt optional. Nähere Informationen zur Domain Verifikations Datei findest du in deinem Checkout portal.

6. Fertig - Die Konfiguration des Assistenten ist damit abgeschlossen. 

### Konfigurationsanleitung - Zahlungsarten in plentymarkets: 
In der neusten Version des Plugins ist es möglich individuelle Zahlungsarten über den Nexi Checkout anzubieten. Dies kannst du im 2. Schritt des Assistenten (Verfügbarkeit) machen. 

Solltest du in deinem System mit Kundenklassen arbeiten, muss die jeweilige Zahlungsart entsprechend für deine Kundenklasse(n) aktiviert werden, erst dann wird diese auch im Checkout sichtbar. (Bitte beachte dass [Apple Pay](https://developer.nexigroup.com/nexi-checkout/en-EU/docs/apple-pay/) nur in bestimmten Browsern/Betriebssystemen angezeigt wird)

1. Die Konfiguration deiner Kundenklasse(n) findest du unter: **”Einrichtung » CRM » Kundenklassen”** 

2. Im Bereich “Erlaubte Zahlungsarten” muss dann die entsprechende Zahlungsart mit den Prefix "Easy" bzw "Nexi" hinzugefügt werden

![NE_Kundenklassen](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_kundenklassen_new.png)

### Konfigurationsanleitung - Ereignisaktionen
Folgende Ereignisaktionen werden dir vom Plugin zur Verfügung gestellt:


1. **Nexi Checkout Belastung der Zahlung** - Dieses Ereignis ermöglicht es dir, eine bereits reservierte Zahlung zu belasten. 

2. **Nexi Checkout Stornierung der Zahlung** - Dieses Ereignis erlaubt es dir, eine reservierte Zahlung zu stornieren. 
(Bitte beachte: Eine bereits belastete Zahlung kann nicht storniert werden.)

3. **Nexi Checkout Rückerstattung der Zahlung** - Dieses Ereignis erlaubt es dir, eine bereits belastete Zahlung zurückzuerstatten. 
(Bitte beachte: Eine Rückerstattung kann nur basierend auf einem Auftrag von Typ Gutschrift erfolgen) 

#### Beispiel: Konfiguration der Ereignisaktionen: 
Grundsätzlich steht es dir frei, wie du die Ereignisaktionen in deinem System konfigurierst. Unsere Empfehlungen beziehen sich auf einen standardisierten Auftragsbearbeitungsprozess. 

1. **Belastung einer Zahlung - Nexi Checkout Belastung der Zahlung**: 
Wir empfehlen dir eine Zahlung zu belasten, sobald die entsprechenden Waren in den Versand übergeben worden sind.

   **Ereignis:** 	Warenausgang gebucht 
   **Filter:** 	    Zahlungsarten, ggf. Auftragstyp
   **Aktion:** 	    Nexi Checkout Belastung der Zahlung

![NE_charge_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_charge_event.png)

2. **Stornieren einer Zahlung - Nexi Checkout Stornierung der Zahlung:**
Das Stornieren einer Zahlung solltest du über deinen Stornierungsstatus abbilden und mit Hilfe von Filtern sicherstellen, dass das Ereignis nur für Aufträge ausgeführt wird, die in Verbindung zu den relevanten Zahlarten stehen.

    **Ereignis:**	Statuswechsel
    **Filter:** 	Zahlungsarten, ggf. Auftragstyp
    **Aktion:**	    Nexi Checkout Stornierung der Zahlung

![NE_cancel_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_cancel_event.png)

3. **Rückerstattung einer Zahlung - Nexi Checkout Rückerstattung der Zahlung:**
Die Rückerstattung einer Zahlung ist entsprechend nach Erhalt einer Retoure relevant. Das Ereignis sollte so konfiguriert werden, dass es für entsprechende Gutschriften ausgeführt werden kann. 
Wir empfehlen hier die Erstellung eines entsprechenden Status im Bereich “Gutschrift” - 11.x 

    **Ereignis:** 	Statuswechsel
    **Filter:** 	Zahlungsarten, ggf. Auftragstyp
    **Aktion:**	    Nexi Checkout Rückerstattung der Zahlung

![NE_refund_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_refund_event.png)