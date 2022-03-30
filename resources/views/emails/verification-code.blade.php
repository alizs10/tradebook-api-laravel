@component('mail::message')
# فعالسازی حساب کاربری

کد فعالسازی: {{ $verification_code }}

{{ config('app.name') }}
@endcomponent