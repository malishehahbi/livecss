# LiveCSS WordPress Plugin Projesi - 20 Günlük Staj Raporu

## Proje Genel Bakış

LiveCSS, WordPress siteleri için gerçek zamanlı önizleme özelliğine sahip görsel bir CSS editörü sağlayan bir WordPress eklentisidir. Proje, WordPress admin çubuğuna "Edit CSS" butonu ekleyerek CSS düzenleme arayüzünü sağlar. Ana bileşenler şunlardır:

- `livecss.php`: Ana eklenti dosyası, temel işlevselliği sağlar
- `templates/`: Editör arayüzünü oluşturan şablon dosyaları
  - `editor.php`: Editörün tüm parçalarını birleştiren ana dosya
  - `editor-header.php`: HTML başlığı ve stiller
  - `editor-content.php`: Görsel editör ve kontrol bileşenleri
  - `editor-js.php`: JavaScript işlevselliği (LiveCSSEditor sınıfı ve başlatma)

### Sistem Mimarisi

Eklenti, WordPress admin çubuğuna "Edit CSS" butonu ekler. Kullanıcı butona tıkladığında `?csseditor=run` parametresiyle CSS editörü ayrı bir sayfada açılır. Editör iki ana modda çalışabilir:

1. **Görsel Editör**: CSS özelliklerini değiştirmek için forma dayalı arayüz
2. **Kod Editörü**: Direct CSS kodlama için CodeMirror tabanlı arayüz

Her iki modda yapılan değişiklikler anında önizlemede gösterilir. Kullanıcı değişiklikleri kaydettiğinde CSS doğrudan `wp_head` aksiyonu ile sitenin frontend'ine enjekte edilir.

## Günlükler

### 1. Gün (14/07/2025) – Proje Tanıtımı ve Kurulum

Stajın ilk gününde LiveCSS WordPress eklentisi projesiyle tanıştım. Projenin yapısı, dosya hiyerarşisi ve genel işleyişi hakkında bilgi edindim. Ana eklenti dosyası olan `livecss.php` dosyasını inceledim ve WordPress ile nasıl entegre olduğunu anladım. Eklentinin bir sınıf olarak yapılandırıldığını ve WordPress eklentileri geliştirme prensiplerine uygun olarak yapılandırıldığını fark ettim.

### 2. Gün (15/07/2025) – WordPress Eklenti Mimarisi

`livecss.php` dosyasındaki constructor ve `init` fonksiyonlarını detaylı olarak inceledim. Eklentinin WordPress'in yaşam döngüsüne nasıl entegre olduğunu ve hangi hook'ların nasıl kullanıldığını öğrendim. Admin bar'a CSS editörü butonunun nasıl eklendiğini ve CSS editörünün ne zaman yükleneceğini belirleyen mantığı analiz ettim.

```php
// Add the "Edit CSS" button to the admin bar
add_action('admin_bar_menu', array($this, 'add_edit_css_button'), 100);

// Check if we're in CSS editor mode
if (isset($_GET['csseditor']) && $_GET['csseditor'] === 'run') {
    add_action('template_redirect', array($this, 'load_css_editor'));
}
```

### 3. Gün (16/07/2025) – CSS Yükleme ve Kaydetme Sistemi

Eklentinin CSS'in frontend'te nasıl yüklendiğini ve AJAX üzerinden nasıl kaydettiğini inceledim. CSS dosyasının `wp-content/uploads/livecss/main.css` konumuna nasıl yazıldığını ve bu dosyanın her sayfa yüklemesinde nasıl ekstra CSS olarak frontend'e nasıl enjekte edildiğini öğrendim.

```php
// Load saved CSS on the frontend
public function load_saved_css() {
    $upload_dir = wp_upload_dir();
    $css_file_path = $upload_dir['basedir'] . '/livecss/main.css';
    $css_file_url = $upload_dir['baseurl'] . '/livecss/main.css';

    if (file_exists($css_file_path) && filesize($css_file_path) > 0) {
        $version = filemtime($css_file_path);
        wp_enqueue_style('livecss-custom', $css_file_url, array(), $version, 'all');
    }
}
```

### 4. Gün (17/07/2025) – CSS Sınıfı Yönetimi Sistemi

`includes/class-css-class-manager.php` dosyasını inceledim ve CSS sınıf yönetimi sisteminin nasıl çalıştığını öğrendim. REST API endpoint'lerinin nasıl tanımlandığını, Gutenberg editörüne nasıl entegre edildiğini ve kullanıcı ayarlarının nasıl yönetildiğini analiz ettim.

### 5. Gün (18/07/2025) – REST API Endpoint'leri

CSS sınıf yönetimi için tanımlanmış REST API endpoint'lerini inceledim. Bu endpoint'lerin yetkilendirme ve izin kontrolünü nasıl gerçekleştirdiğini ve veri giriş-çıkışlarını nasıl yönettiğini öğrendim.

```php
// Register REST API routes
public function register_rest_routes() {
    // Get CSS classes
    register_rest_route('livecss/v1', '/css-classes', array(
        'methods' => 'GET',
        'callback' => array($this, 'get_css_classes'),
        'permission_callback' => array($this, 'check_permissions')
    ));
    // ... diğer endpoint'ler
}
```

### 6. Gün (19/07/2025) – Gutenberg Entegrasyonu

Gutenberg editörüne CSS sınıf seçiciyi entegre etme sürecini inceledim. Block editor assets'in nasıl yüklendiğini, JavaScript bileşenlerinin nasıl React ile yapılandırıldığını ve CSS sınıflarının nasıl uygulandığını öğrendim.

### 7. Gün (20/07/2025) – Editör Arayüzü ve HTML Yapısı

`templates/editor-content.php` dosyasını inceledim ve editörün görsel bileşenlerinin HTML yapısını analiz ettim. Editör panelindeki accordion yapılarının nasıl tanımlandığını ve farklı CSS özellikleri için nasıl kontroller oluşturulduğunu öğrendim.

### 8. Gün (21/07/2025) – Görsel Editör Kontrolleri

Farklı CSS kategorileri için tanımlanmış kontrolleri inceledim. Typografi, arka plan, boyutlandırma, düzen gibi kategorilerin nasıl yapılandırıldığını ve her bir kategori için hangi CSS özellikleri için hangi tip kontrollerin kullanıldığını inceledim.

### 9. Gün (22/07/2025) – JavaScript Editör Sınıfı

`templates/editor-js.php` dosyasında tanımlanmış `LiveCSSEditor` JavaScript sınıfını inceledim. CSS özelliklerinin nasıl güncellendiğini, CSS'in nasıl üretilip önizlemede nasıl gösterildiğini öğrendim.

### 10. Gün (23/07/2025) – CSS Üretimi ve Parsing

CSS parsing algoritmasını ve CSS üretimi sürecini detaylı olarak inceledim. Farklı CSS değerlerinin nasıl parse edildiğini ve CSS string'lerinin nasıl oluşturulduğunu öğrendim. Özellikle transform ve filter gibi özel özelliklerin nasıl işlendiğini inceledim.

### 11. Gün (24/07/2025) – Pseudo-class Desteği

Editörde hover, focus, active gibi pseudo-class'ların nasıl desteklendiğini inceledim. Bu sınıflar için eklenen butonların nasıl çalıştığını ve CSS selector'lerinin pseudo-class'larla nasıl birleştirildiğini analiz ettim.

### 12. Gün (25/07/2025) – Cihaz Uyumlu Önizleme

Mobil, tablet ve masaüstü cihazlar için farklı önizlemelerin nasıl yapıldığını inceledim. Cihaz simülasyonu sağlayan butonların nasıl çalıştığını ve önizleme iframe'inin nasıl boyutlandırıldığını öğrendim.

### 13. Gün (26/07/2025) – Kod Editörü ve CodeMirror Entegrasyonu

CodeMirror tabanlı kod editörünün nasıl entegre edildiğini inceledim. Kod editörünün nasıl başlatıldığını, CSS değişikliklerinin nasıl izlendiğini ve görsel editörle nasıl senkronize edildiğini öğrendim.

### 14. Gün (27/07/2025) – CSS Seçici ve Element Tıklama Sistemi

CSS selector giriş alanını ve element tıklama sistemini inceledim. Kullanıcının önizlemedeki elementlere tıklayarak otomatik CSS selector'lerin nasıl oluşturulduğunu analiz ettim.

### 15. Gün (28/07/2025) – Arama ve Bul

`docs/SEARCH-FUNCTIONALITY.md` dosyasını inceledim ve editördeki arama özelliğini analiz ettim. Görsel editörde özelliklere göre arama ve kod editöründe CSS içeriğine göre arama nasıl yapıldığını öğrendim.

### 16. Gün (29/07/2025) – Özellik Bağımlılıkları

`docs/PROPERTY-DEPENDENCIES.md` dosyasını inceledim ve CSS özelliklerinin birbirlerine nasıl bağlı olduğunu öğrendim. Örneğin, pozisyon özelliklerinin sadece belirli position değerlerinde görünmesi gibi durumların nasıl uygulandığını analiz ettim.

```html
<!-- Show when position is NOT "static" or empty -->
<div class="control-group" data-depends-on="position" data-depends-value="!static,!">
    <label class="control-label">Top</label>
    <input type="text" class="control" data-property="top" placeholder="10px, 1em, 50%">
</div>
```

### 17. Gün (30/07/2025) – Kayıtlı Değişiklikler ve Geçmiş Sistemi

`docs/UNSAVED-CHANGES-AND-HISTORY.md` dosyasını inceledim ve değişikliklerin nasıl izlendiğini ve geçmişin nasıl yönetildiğini öğrendim. Kullanıcının kaydetmediği değişiklikleri nasıl koruduğunu ve undo/redo özelliklerinin nasıl çalıştığını analiz ettim.

### 18. Gün (31/07/2025) – CSS Sınıfı Yönetimi UI

CSS sınıf yönetimi arayüzünü ve modal penceresinin nasıl çalıştığını inceledim. Kullanıcının nasıl yeni CSS sınıfları ekleyip yönettiğini ve bu sınıfların Gutenberg editörüne nasıl entegre edildiğini öğrendim.

### 19. Gün (01/08/2025) – Güvenlik Önlemleri

Eklentide kullanılan güvenlik önlemlerini inceledim. Nonce doğrulamaları, kullanıcı yetkilendirme kontrolleri ve CSS sanitizasyonu gibi güvenlik önlemlerinin nasıl uygulandığını analiz ettim.

### 20. Gün (02/08/2025) – Performans ve Optimizasyon

Tüm proje boyunca öğrendiğim bilgileri özetledim ve eklentinin genel performansı üzerindeki etkilerini değerlendirdim. CSS minification, dosya sistemi işlemleri ve frontend performansı gibi konuları ele aldım. Ayrıca, gelecekte eklentiye eklenebilecek yeni özellikler ve geliştirilebilecek alanlar hakkında fikirler geliştirdim.