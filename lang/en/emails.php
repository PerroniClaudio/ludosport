<?php

return [

    /** Fee */

    'fee_email_title' => 'Hello :name,',
    'fee_email_introduction' => 'Thanks for joining ' . config('app.name') . '. Your fee has been paid successfully and you now have access to the events!',
    'fee_email_order_details' => 'Order details',
    'fee_email_order_number' => 'Order number',
    'fee_email_order_date' => 'Order date',
    'fee_email_membership_type' => 'Fee type',
    'fee_email_total' => 'Total',
    'fee_email_payment_method' => 'Payment method',
    'fee_email_fee_validity_title' => 'Fee validity',
    'fee_email_fee_validity_text' => 'Your fee is valid until :date. We will send you a reminder before it expires.',
    'fee_email_how_to_log_in' => 'How to log in',
    'fee_email_log_in_instructions' => 'You can log in to your account by clicking the button below.',
    'fee_email_log_in_instructions_alt' => 'If you have any issues with the button above, you can also log in by copying and pasting the following link into your browser:',
    'fee_email_regards' => 'Regards,',

    /** Bulk Fee */

    'bulk_fee_email_title' => 'Hello :name,',
    'bulk_fee_email_introduction' => 'Thanks for your purchases. Your fees have been paid successfully and can now be associated to users!',
    'bulk_fee_email_order_details' => 'Order details',
    'bulk_fee_email_order_number' => 'Order number',
    'bulk_fee_email_order_date' => 'Order date',
    'bulk_fee_email_total' => 'Total',
    'bulk_fee_email_payment_method' => 'Payment method',
    'bulk_fee_email_fee_validity_title' => 'Fee validity',
    'bulk_fee_email_fee_validity_text' => 'Your fees are valid until :date. We will send you a reminder before they expire.',
    'bulk_fee_email_fee_purchased_items' => 'Purchased items',

    /** Event Participation */

    'event_participation_email_title' => 'Hello :name,',
    'event_participation_email_introduction' => 'Thanks for signing up to the event :event. Your event participation has been confirmed successfully!',
    'event_participation_from_waiting_list_email_introduction' => 'Thanks for signing up to the event :event. Your spot has become available and your event participation has been confirmed successfully!',
    'event_participation_email_order_details' => 'Order details',
    'event_participation_email_order_number' => 'Order number',
    'event_participation_email_order_date' => 'Order date',
    'event_participation_email_total' => 'Total',
    'event_participation_email_payment_method' => 'Payment method',
    'event_participation_email_event_details' => 'Event details',
    'event_participation_email_event_name' => 'Event name',
    'event_participation_email_event_date' => 'Event date',
    'event_participation_email_event_location' => 'Event location',
    
    /** Event Waiting list add */

    'event_waiting_list_add_email_title' => 'Hello :name,',
    'event_waiting_list_add_email_introduction' => 'Thanks for joining the waiting list of the event :event. We will notify you if a spot becomes available!',
    'event_waiting_list_add_email_future_payment' => 'If a spot becomes available, you will be asked to pay the fee to confirm your participation.',
    'event_waiting_list_add_email_total' => 'Total',
    'event_waiting_list_add_email_event_details' => 'Event details',
    'event_waiting_list_add_email_event_name' => 'Event name',
    'event_waiting_list_add_email_event_date' => 'Event date',
    'event_waiting_list_add_email_event_location' => 'Event location',

    /** Event Must pay */

    'event_must_pay_email_title' => 'Hello :name,',
    'event_must_pay_email_introduction' => 'A spot in the event ":event" is now available. Please pay the fee to confirm your participation.',
    'event_must_pay_email_future_payment' => 'If you do not pay the fee within 5 days, the spot will be given to the next person on the waiting list.',
    'event_must_pay_email_details' => 'Details',
    'event_must_pay_email_total' => 'Total',
    'event_must_pay_email_pay_now' => 'Pay now',
    'event_must_pay_email_deadline' => 'Payment deadline',
    'event_must_pay_email_event_details' => 'Event details',
    'event_must_pay_email_event_name' => 'Event name',
    'event_must_pay_email_event_date' => 'Event date',
    'event_must_pay_email_event_location' => 'Event location',
    'event_must_pay_email_regards' => 'Regards,',
    
    /** Event Waiting list remove */
    
    'event_waiting_list_remove_email_title' => 'Hello :name,',
    'event_waiting_list_remove_email_introduction' => 'Since the deadline for payment has passed, your spot in the event :event has been given to the next person on the waiting list.',
    'event_waiting_list_remove_email_regards' => 'Regards,',

    /** User creation */
    'created_user_email_title' => 'Hello :name,',
    'created_user_email_introduction' => 'Your account has been created successfully! You can now set up your password to proceed further.',
    'created_user_setup_password' => 'Set up your password',

];
