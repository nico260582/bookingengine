# Booking Manager Joomla Component

A Joomla 3.x component to manage booking requests and suppliers. This component is designed for holiday booking websites, allowing administrators to handle booking inquiries, manage property suppliers, set rates, and communicate with clients and suppliers efficiently.

## Features

-   **Booking Request Management:** A comprehensive system to manage all booking requests from clients.
-   **Supplier Management:** Manage a list of property suppliers and their specific contact details and rules.
-   **Property Rates:** Define property rates based on different seasons.
-   **Email & WhatsApp Templates:** Customizable templates for all communication with clients and suppliers.
-   **Client Portal:** A secure portal for clients to view and manage their booking requests.
-   **Communication Log:** Keeps a record of all messages exchanged for a booking.
-   **File Attachments:** Allows attaching files to booking requests.
-   **Audit Logs:** Logs all changes to booking requests and supplier details for full traceability.
-   **Diagnostic Tools:** A dedicated view to help diagnose and troubleshoot potential issues.

## Database Schema

The component creates the following tables in the Joomla database:

-   `#__booking_requests`: Stores all the details of booking requests made by clients.
-   `#__booking_communication`: Contains the history of messages exchanged between the administrator, client, and supplier for each booking request.
-   `#__booking_attachments`: Manages file attachments associated with booking requests.
-   `#__booking_request_logs`: Provides an audit trail of changes made to booking requests.
-   `#__booking_supplier_logs`: Provides an audit trail of changes made to supplier records.
-   `#__bookingmanager_suppliers`: Stores information about the property suppliers, including their contact details and custom business rules.
-   `#__bookingmanager_property_map`: Maps properties to their respective suppliers.
-   `#__bookingmanager_rates`: Stores property rates, which can be defined for different seasons.
-   `#__bookingmanager_templates`: Contains customizable templates for emails and WhatsApp messages sent by the system.

## Installation

1.  Package the component files into a ZIP archive.
2.  Log in to your Joomla Administrator panel.
3.  Navigate to `Extensions` -> `Manage` -> `Install`.
4.  Under the `Upload Package File` tab, select the ZIP file you created.
5.  The component will be installed automatically.

## Configuration and Usage

After installation, you can access the component's administration interface from the `Components` -> `Booking Manager` menu in your Joomla backend.

The component is managed through the following views:

-   **Booking Requests:** The main dashboard to view, manage, and respond to all booking inquiries.
-   **Suppliers:** Add and manage property suppliers, their contact information, and specific business rules (e.g., pricing models, regional discounts).
-   **Property Rates:** Define seasonal rates for properties.
-   **Email Templates:** Customize the content of emails and WhatsApp messages sent to clients, admins, and suppliers.
-   **Diagnostic:** A tool to check the component's status and troubleshoot issues.
