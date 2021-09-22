<?php

namespace App\Contracts;

interface PaymentService
{
    public function handlePayment(array $validated);
    public function handleApproval();
    public function handleSubscription();
}
