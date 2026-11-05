@extends('legal.layout')

@section('title', 'Terms of service')

@section('content')
    <p>These terms explain the rules for using this inventory system, run by {{ $businessName }}.</p>

    <h2>1. Who can use it</h2>
    <p>The system is for the business that runs it and the people that business gives an account to. Keep your
        password private and tell the account owner straight away if you think someone else has used it.</p>

    <h2>2. Your data</h2>
    <p>Everything you enter (products, customers, suppliers, orders, invoices and accounts) belongs to {{ $businessName }}.
        You are responsible for checking that what you enter is correct, especially prices, taxes and amounts.</p>

    <h2>3. Acceptable use</h2>
    <ul>
        <li>Do not try to get into accounts or data that are not yours.</li>
        <li>Do not upload files that contain viruses or that you have no right to share.</li>
        <li>Do not use the system for anything illegal.</li>
    </ul>

    <h2>4. Availability and backups</h2>
    <p>We aim to keep the system running at all times, but there can be short breaks for updates or problems with
        the hosting provider. A copy of the database is saved every day. You should also download your own reports
        regularly.</p>

    <h2>5. Accounts and tax</h2>
    <p>The system helps you keep records. It does not give legal, tax or accounting advice. Check important
        figures with your accountant before filing returns.</p>

    <h2>6. Changes</h2>
    <p>These terms may be updated. The date at the top of this page shows when they last changed.</p>

    <h2>7. Contact</h2>
    <p>Questions about these terms: {{ $contactEmail ?: 'contact the account owner' }}.</p>
@endsection
