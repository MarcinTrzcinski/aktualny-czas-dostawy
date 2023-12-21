<?php
/*
Plugin Name: Aktualny czas dostawy
Description: Dodaje funkcje włączenia/wyłączenia aktualnego czasu dostawy na karcie produktu.
Version: 1.0
Author: Marcin Trzciński
Author URI: https://marcintrzcinski.pl/
*/

// Funkcja dodająca opcję włączenia/wyłączenia komunikatu w ustawieniach WooCommerce
function dodaj_opcje_wyswietlania_komunikatu_bl() {
    add_settings_section('wyswietl_komunikat_section', 'Komunikat o terminie dostawy', 'ustawienia_komunikatu_sekcja', 'general');

    add_settings_field(
        'wyswietl_komunikat_terminu_dostawy_bl',
        'Włącz komunikat o terminie dostawy na stronie produktu',
        'ustawienia_wyswietlania_komunikatu_bl',
        'general',
        'wyswietl_komunikat_section'
    );
    add_settings_field(
        'tresc_komunikatu_dostawy',
        'Treść komunikatu o terminie dostawy',
        'ustawienia_tresci_komunikatu',
        'general',
        'wyswietl_komunikat_section'
    );
    add_settings_field(
        'liczba_dni_dostawy_bl',
        'Liczba dni na dostawę (dla wszystkich produktów)',
        'ustawienia_liczby_dni_dostawy_bl',
        'general',
        'wyswietl_komunikat_section'
    );

    add_settings_field(
        'miejsce_wyswietlania_komunikatu',
        'Miejsce wyświetlania komunikatu na stronie produktu',
        'ustawienia_miejsca_wyswietlania_komunikatu',
        'general',
        'wyswietl_komunikat_section'
    );

    register_setting('general', 'wyswietl_komunikat_terminu_dostawy_bl', 'moja_sanitize_checkbox');
    register_setting('general', 'tresc_komunikatu_dostawy');
    register_setting('general', 'liczba_dni_dostawy_bl');
    register_setting('general', 'miejsce_wyswietlania_komunikatu', 'moja_sanitize_text_field');
}
add_action('admin_init', 'dodaj_opcje_wyswietlania_komunikatu_bl');

// Funkcje wyświetlające pola w ustawieniach WooCommerce
function ustawienia_komunikatu_sekcja() {
    echo '<p>Ustawienia komunikatu o terminie dostawy:</p>';
}
function ustawienia_tresci_komunikatu() {
    $option = get_option('tresc_komunikatu_dostawy', 'Termin dostawy: %s dni');
    echo '<input type="text" class="regular-text ltr" id="tresc_komunikatu_dostawy" name="tresc_komunikatu_dostawy" value="' . esc_attr($option) . '" />';
}
function ustawienia_wyswietlania_komunikatu_bl() {
    $option = get_option('wyswietl_komunikat_terminu_dostawy_bl', 'no');
    echo '<input type="checkbox" id="wyswietl_komunikat_terminu_dostawy_bl" name="wyswietl_komunikat_terminu_dostawy_bl" ' . checked('yes', $option, false) . ' value="yes" />';
}

function ustawienia_liczby_dni_dostawy_bl() {
    $option = get_option('liczba_dni_dostawy_bl', 3);
    echo '<input type="number" id="liczba_dni_dostawy_bl" name="liczba_dni_dostawy_bl" value="' . esc_attr($option) . '" />';
}

function ustawienia_miejsca_wyswietlania_komunikatu() {
    $option = get_option('miejsce_wyswietlania_komunikatu', 'after_single_product');
    ?>
    <select id="miejsce_wyswietlania_komunikatu" name="miejsce_wyswietlania_komunikatu">
        <option value="aktualny_czas_dostawy" <?php selected('aktualny_czas_dostawy', $option); ?>>Custom (hook: aktualny_czas_dostawy)</option>
        <option value="woocommerce_single_product_summary" <?php selected('woocommerce_single_product_summary', $option); ?>>Przed przyciskiem "Dodaj do koszyka"</option>
        <option value="after_single_product" <?php selected('after_single_product', $option); ?>>Po treści produktu</option>
        <option value="woocommerce_before_single_product" <?php selected('woocommerce_before_single_product', $option); ?>>Przed treścią produktu</option>
        <option value="woocommerce_after_single_product" <?php selected('woocommerce_after_single_product', $option); ?>>Po całej karcie produktu</option>
    </select>
    <?php
}

// Sanitize checkbox value
function moja_sanitize_checkbox($input) {
    return ($input === 'yes') ? 'yes' : 'no';
}

// Sanitize text field value
function moja_sanitize_text_field($input) {
    return sanitize_text_field($input);
}

// Wyświetla informacje o dostawie na karcie produktu
function wyswietl_termin_dostawy_bl() {
    $wyswietl_komunikat_bl = get_option('wyswietl_komunikat_terminu_dostawy_bl', 'no');
    $tresc_komunikatu = get_option('tresc_komunikatu_dostawy', 'Termin dostawy: %s dni');
    $liczba_dni_dostawy_bl = get_option('liczba_dni_dostawy_bl', 3);
    $miejsce_wyswietlania_komunikatu = get_option('miejsce_wyswietlania_komunikatu', 'after_single_product');

    if ($wyswietl_komunikat_bl === 'yes') {
        $formatted_message = sprintf($tresc_komunikatu, esc_html($liczba_dni_dostawy_bl));
        ?>
        <li class="d-align">
            <div class="single-feature-img">
                <noscript><img decoding="async" src="https://zwieger.pl/wp-content/uploads/2022/10/termin_dostawy.svg" alt="Zwieger"></noscript>
                <img decoding="async" class=" ls-is-cached lazyloaded" src="https://zwieger.pl/wp-content/uploads/2022/10/termin_dostawy.svg" data-src="https://zwieger.pl/wp-content/uploads/2022/10/termin_dostawy.svg" alt="Zwieger">
            </div>
            <div class="single-feature-info">
                <h4>Termin dostawy</h4> 
                <span><?php echo $formatted_message; ?></span>
            </div>
        </li>
        <?php
    }
}

// Dodaje akcję w zależności od wybranego miejsca wyświetlania komunikatu
if (get_option('wyswietl_komunikat_terminu_dostawy_bl', 'no') === 'yes') {
    $miejsce_wyswietlania_komunikatu = get_option('miejsce_wyswietlania_komunikatu', 'after_single_product');
    if ($miejsce_wyswietlania_komunikatu === 'woocommerce_single_product_summary') {
        add_action('woocommerce_single_product_summary', 'wyswietl_termin_dostawy_bl');
    } elseif ($miejsce_wyswietlania_komunikatu === 'after_single_product') {
        add_action('woocommerce_after_single_product', 'wyswietl_termin_dostawy_bl');
    } elseif ($miejsce_wyswietlania_komunikatu === 'woocommerce_before_single_product') {
        add_action('woocommerce_before_single_product', 'wyswietl_termin_dostawy_bl');
    } elseif ($miejsce_wyswietlania_komunikatu === 'woocommerce_after_single_product') {
        add_action('woocommerce_after_single_product', 'wyswietl_termin_dostawy_bl');
    } elseif ($miejsce_wyswietlania_komunikatu === 'aktualny_czas_dostawy') {
        add_action('aktualny_czas_dostawy', 'wyswietl_termin_dostawy_bl');
    }
}


?>