
# Doctor-Patient Appointment Booking System

## Overview

The **Doctor-Patient Appointment Booking System** is a web-based application that allows patients to book appointments with doctors, manage schedules, and handle medical concerns. It provides an efficient way for both patients and doctors to manage appointments.

This system is built using **VaahCMS** as the backend framework and **Vue.js 3** for the frontend, with **PrimeVue** components for UI, along with **Pinia** for state management.

---

## Features

- **Doctor and Patient Management**: View, add, and manage doctor and patient details.
- **Appointment Booking**: Book, view, edit, cancel, reschedule appointments.
- **Email Notifications**: Send automated appointment confirmations and reminders.
- **Responsive Design**: As per project requirement we have made the only Appointment section responsive.
- **CSV Export**: Export appointment data.

---

## Technologies Used

### Backend
- [Vaahcms](https://vaah.dev/)
- [MySQL](https://www.mysql.com/) (Database)

### Frontend
- [Vue.js 3](https://vuejs.org/)
- [Pinia](https://pinia.vuejs.org/) (State Management)
- [PrimeVue](https://www.primefaces.org/primevue/) (UI Components)

---

## Setup Instructions

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL or another relational database
- npm

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/we-sunil-k001/DoctorPatient-Appointment-Booking-System/tree/develop
   ```


## Usage

- **Patients**: Patients can browse available doctors, select a time slot, and book an appointment.
- **Doctors**: Doctors can view their appointments, approve/reject bookings, and manage their schedules.
- **Conflict Detection**: The system will automatically prevent patients from booking appointments that overlap or are within one hour of another appointment.

---

## Responsive Design

 As per project requirement we have made the only Appointment section responsive, allowing doctors and patients to manage their appointments seamlessly across desktop and mobile devices. For smaller screens, data is presented in a mobile-friendly card layout.

---

## Export Functionality

You can export appointment data to a CSV file using two options:
1. Download only the CSV headers (useful for templates).
2. Download the full data with headers and appointment information.

---

---

## Contact

For any inquiries or support, please contact:

- Email: sunil-k001@webreinvent.com
- GitHub: [Sunil Kumar](https://github.com/we-sunil-k001)
