@extends('layouts.master')

@section('title', 'Astro Email')

@section('content')

    <h3>Send Email</h3>

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.send_mail.send') }}">
        @csrf

        <div>
            <label>Name:</label><br>
            <input type="text" name="name" placeholder="Name" required>
        </div>

        <br>

        <div>
            <label>Email:</label><br>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <br>

        <button type="submit">Send Mail</button>
    </form>

@endsection
