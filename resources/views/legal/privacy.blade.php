@extends('legal.layout')

@section('title', 'Privacy policy')

@section('content')
    <p>This page explains what personal information this inventory system keeps and how it is protected.
        {{ $businessName }} is responsible for this information.</p>

    <h2>What we keep</h2>
    <ul>
        <li>Your sign-in details: name, email address and a scrambled (hashed) version of your password. The password
            itself is never stored.</li>
        <li>Business records you enter, which may include names, phone numbers, email addresses and addresses of
            customers and suppliers.</li>
        <li>Basic technical records needed to keep the system safe, such as the time of sign-in.</li>
    </ul>

    <h2>Why we keep it</h2>
    <p>Only to run the business: to manage stock, sales, purchases, invoices and accounts. It is not sold, rented or
        shared for advertising.</p>

    <h2>Where it is kept</h2>
    <p>On the server that hosts this system. Connections are encrypted (HTTPS). The system does not send your
        data to any outside service.</p>

    <h2>Cookies</h2>
    <p>The system uses only the cookies it needs to keep you signed in and to protect forms against misuse.
        There are no advertising or tracking cookies.</p>

    <h2>How long we keep it</h2>
    <p>As long as the business needs it for its records and for the time the law requires for accounts.
        Daily database copies are kept for 14 days.</p>

    <h2>Your choices</h2>
    <p>You can ask to see, correct or delete personal information about you by writing to
        {{ $contactEmail ?: 'the account owner' }}.</p>
@endsection
