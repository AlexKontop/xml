// Hook που ενεργοποιείται όταν ολοκληρώνεται μια παραγγελία
add_action('woocommerce_order_status_completed', 'send_notification_on_order_complete');

function send_notification_on_order_complete($order_id) {
    $order = wc_get_order($order_id);
    
    // Ανάκτηση πληροφοριών παραγγελίας
    $order_items = $order->get_items();
    $products = [];
    foreach ($order_items as $item_id => $item) {
        $products[] = $item->get_name() . ' (Ποσότητα: ' . $item->get_quantity() . ')';
    }
    $product_list = implode(", ", $products);

    // Ετοιμασία μηνύματος (Απλό κείμενο, χωρίς JSON συμβολισμούς)
    $order_details = "Νέα ολοκληρωμένη παραγγελία:\n\n" .
                     "Κωδικός Παραγγελίας: " . $order->get_id() . "\n" .
                     "Πελάτης: " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "\n" .
                     "Email: " . $order->get_billing_email() . "\n" .
                     "Διεύθυνση Αποστολής: " . $order->get_shipping_address_1() . ", " . $order->get_shipping_city() . "\n" .
                     "Προϊόντα: " . $product_list . "\n" .
                     "Σύνολο: " . $order->get_total() . " " . get_woocommerce_currency() . "\n";

    // Ενσωμάτωση ntfy API
    $ntfy_url = 'https://ntfy.sh/petowino-orders'; // Αντικαταστήστε με το ntfy topic σας

    // Χρήση cURL για την αποστολή ως απλό κείμενο
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ntfy_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $order_details); // Αποστολή ως απλό κείμενο
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Title: Παραγγελία Έτοιμη για Πτήση!')); // Τίτλος ειδοποίησης
    $response = curl_exec($ch);
    curl_close($ch);
}