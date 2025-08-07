# Joomla Booking Management System

This repository contains a Joomla booking management system consisting of a component (`com_bookingmanager`) and a module (`mod_bookingform`). This system is designed for holiday booking websites, allowing administrators to handle booking inquiries, manage property suppliers, set rates, and communicate with clients and suppliers efficiently.

## Features

### `com_bookingmanager` (Component)

The core of the system, providing a comprehensive backend interface for managing bookings.

-   **Booking Request Management:** A comprehensive system to manage all booking requests from clients.
-   **Supplier Management:** Manage a list of property suppliers and their specific contact details and rules.
-   **Property Rates:** Define property rates based on different seasons.
-   **Email & WhatsApp Templates:** Customizable templates for all communication with clients and suppliers.
-   **Client Portal:** A secure portal for clients to view and manage their booking requests.
-   **Communication Log:** Keeps a record of all messages exchanged for a booking.
-   **File Attachments:** Allows attaching files to booking requests.
-   **Audit Logs:** Logs all changes to booking requests and supplier details for full traceability.
-   **Diagnostic Tools:** A dedicated view to help diagnose and troubleshoot potential issues.

### `mod_bookingform` (Module)

A lightweight front-end module that provides a compact and user-friendly booking form.

-   **Seamless Integration:** Connects directly with `com_bookingmanager` to fetch real-time pricing and rules.
-   **Dynamic Pricing:** Calculates prices based on seasonal rates, number of guests, and other rules defined in the component.
-   **Simple User Interface:** Easy for clients to fill out and submit booking inquiries.

## Installation

To use this booking system, you need to install both the component and the module.

### 1. Package the Extensions

You will need to create two separate ZIP files for installation:

-   **Component (`com_bookingmanager`):** Create a ZIP archive of the `src_component` directory. You can rename the ZIP file to `com_bookingmanager.zip`.
-   **Module (`mod_bookingform`):** Create a ZIP archive of the `mod_bookingform` directory. You can rename the ZIP file to `mod_bookingform.zip`.

### 2. Install in Joomla

1.  Log in to your Joomla Administrator panel.
2.  Navigate to `Extensions` -> `Manage` -> `Install`.
3.  Under the `Upload Package File` tab, select the `com_bookingmanager.zip` file and install it.
4.  Repeat the process for the `mod_bookingform.zip` file.

## Configuration and Usage

### Component (`com_bookingmanager`)

After installation, you can access the component's administration interface from the `Components` -> `Booking Manager` menu in your Joomla backend.

The component is managed through the following views:

-   **Booking Requests:** The main dashboard to view, manage, and respond to all booking inquiries.
-   **Suppliers:** Add and manage property suppliers, their contact information, and specific business rules (e.g., pricing models, regional discounts).
-   **Property Rates:** Define seasonal rates for properties.
-   **Email Templates:** Customize the content of emails and WhatsApp messages sent to clients, admins, and suppliers.
-   **Diagnostic:** A tool to check the component's status and troubleshoot issues.

### Module (`mod_bookingform`)

1.  Navigate to `Extensions` -> `Modules`.
2.  Find `Booking Form` in the list and open it.
3.  Assign the module to a specific position on your website and set the pages where it should appear.
4.  Configure the basic settings, such as the currency symbol.

## Database Schema

The `com_bookingmanager` component creates the following tables in the Joomla database:

-   `#__booking_requests`: Stores all the details of booking requests made by a client.
-   `#__booking_communication`: Contains the history of messages exchanged between the administrator, client, and supplier for each booking request.
-   `#__booking_attachments`: Manages file attachments associated with booking requests.
-   `#__booking_request_logs`: Provides an audit trail of changes made to booking requests.
-   `#__booking_supplier_logs`: Provides an audit trail of changes made to supplier records.
-   `#__bookingmanager_suppliers`: Stores information about the property suppliers, including their contact details and custom business rules.
-   `#__bookingmanager_property_map`: Maps properties to their respective suppliers.
-   `#__bookingmanager_rates`: Stores property rates, which can be defined for different seasons.
-   `#__bookingmanager_templates`: Contains customizable templates for emails and WhatsApp messages sent by the system.
