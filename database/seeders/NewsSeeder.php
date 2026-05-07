<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->firstOrFail();
        $categories = Category::query()->get()->keyBy('slug');

        $tagDefs = [
            'Merkez Bankası' => 'merkez-bankasi',
            'Türkiye-AB' => 'turkiye-ab',
            'Galatasaray' => 'galatasaray',
            'Yapay zeka' => 'yapay-zeka',
            'Döviz' => 'doviz',
            'İstanbul' => 'istanbul',
            'Film festivali' => 'film-festivali',
            'Milli takım' => 'milli-takim',
            'TOGG' => 'togg',
            'İklim' => 'iklim',
        ];
        $tags = collect($tagDefs)->mapWithKeys(fn (string $slug, string $name) => [
            $name => Tag::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            ),
        ]);

        $byCategory = $this->articleDefinitions();
        $order = 0;

        foreach ($byCategory as $slug => $articles) {
            $category = $categories->get($slug);
            if (! $category) {
                continue;
            }

            foreach ($articles as $article) {
                $order++;
                $publishedAt = now()->subHours($order * 2);

                $news = News::query()->updateOrCreate(
                    ['slug' => $article['slug']],
                    [
                        'author_id' => $author->id,
                        'category_id' => $category->id,
                        'title' => $article['title'],
                        'excerpt' => $article['excerpt'],
                        'content' => $article['content'],
                        'thumbnail' => null,
                        'thumbnail_caption' => null,
                        'is_published' => true,
                        'is_breaking' => (bool) ($article['breaking'] ?? false),
                        'view_count' => (int) ($article['views'] ?? random_int(800, 48000)),
                        'published_at' => $publishedAt,
                    ]
                );

                $tagKeys = $article['tags'] ?? [];
                $syncIds = $tags->only($tagKeys)->values()->pluck('id')->all();
                if ($syncIds !== []) {
                    $news->tags()->syncWithoutDetaching($syncIds);
                }
            }
        }
    }

    /**
     * @return array<string, list<array{title: string, slug: string, excerpt: string, content: string, breaking?: bool, views?: int, tags?: list<string>}>>
     */
    private function articleDefinitions(): array
    {
        $p = fn (string $body) => '<p>'.htmlspecialchars($body, ENT_QUOTES, 'UTF-8').'</p>';

        return [
            'gundem' => [
                [
                    'title' => 'Türkiye-AB Zirvesi: Yeni Dönemin Kapısı Aralanıyor',
                    'slug' => 'turkiye-ab-zirvesi-yeni-donemin-kapisi-aralaniyor',
                    'excerpt' => 'Ankara\'da gerçekleşen üst düzey görüşmelerde iki taraf, ticaret ve enerji alanlarında iş birliğini derinleştirmek için yol haritası üzerinde uzlaştı.',
                    'content' => $p('Ankara\'da düzenlenen Türkiye-AB zirvesinde liderler, gümrük birliğinin güncellenmesi ve yeşil dönüşümde ortak adımlar konusunda prensipte anlaştı. Diplomasi kaynakları, müzakere takviminin önümüzdeki aylarda netleşeceğini belirtti.')
                        .$p('Zirve sonrası ortak bildirgede, göç yönetiminde iş birliği ve karşılıklı güven artırıcı adımların sürdürüleceği vurgulandı.'),
                    'breaking' => true,
                    'views' => 48200,
                    'tags' => ['Türkiye-AB'],
                ],
                [
                    'title' => 'İstanbul\'da Yeni Metro Hattı İçin Zemin Etüdü Başladı',
                    'slug' => 'istanbulda-yeni-metro-hatti-icin-zemin-etudu-basladi',
                    'excerpt' => 'Kadıköy-Maltepe arasında planlanan hat, günlük 1,2 milyon yolcuya hizmet vermeyi hedefliyor.',
                    'content' => $p('İstanbul Büyükşehir Belediyesi, yeni metro güzergâhında zemin etüdü ve jeoteknik çalışmaların başladığını duyurdu. Proje kapsamında sondaj noktaları belirlendi.')
                        .$p('Yetkililer, çevresel etki değerlendirmesi sürecinin de paralel yürütüldüğünü ve hat tamamlandığında bölgedeki trafik yükünün belirgin biçimde azalacağını ifade etti.'),
                    'tags' => ['İstanbul'],
                ],
                [
                    'title' => 'Meclis\'te Yeni Düzenleme Paketi Görüşmeleri Sürüyor',
                    'slug' => 'mecliste-yeni-duzenleme-paketi-gorusmeleri-suruyor',
                    'excerpt' => 'Kamu hizmetlerinde dijitalleşmeyi hızlandıran madde tasarısı komisyonda altıncı kez ele alındı.',
                    'content' => $p('TBMM ilgili komisyonunda görüşülen paket, vatandaş başvurularının tek pencereden yürütülmesini ve sürelerin kısaltılmasını öngörüyor.')
                        .$p('Muhalefet partileri, denetim mekanizmalarının güçlendirilmesi için ek öneriler sundu; görüşmeler gelecek hafta devam edecek.'),
                ],
                [
                    'title' => 'Ege Bölgesi\'nde Deprem Tatbikatı Rekor Katılımla Tamamlandı',
                    'slug' => 'ege-bolgesinde-deprem-tatbikati-rekor-katilimla-tamamlandi',
                    'excerpt' => 'Afet yönetimi ekipleri ve gönüllüler, senaryoya uygun tahliye ve ilk yardım senaryolarını uyguladı.',
                    'content' => $p('İzmir ve çevre illerde eş zamanlı düzenlenen tatbikatta binlerce kişi görev aldı. Senaryoda deprem sonrası enerji ve iletişim hatlarının kesintiye uğraması test edildi.')
                        .$p('Uzmanlar, okul ve iş yerlerinde periyodik tatbikatların farkındalığı artırdığına dikkat çekti.'),
                ],
                [
                    'title' => 'Ulaşımda Kartlı Ödeme Sistemleri Yaygınlaşıyor',
                    'slug' => 'ulasimda-kartli-odeme-sistemleri-yayginlasiyor',
                    'excerpt' => 'Birçok ilde toplu taşımada temassız ödeme oranı son bir yılda yüzde 40 arttı.',
                    'content' => $p('Ulaştırma Bakanlığı verilerine göre, mobil cüzdan ve banka kartı ile yapılan ödemeler özellikle büyükşehirlerde hızla yayılıyor.')
                        .$p('Yolcuların bekleme sürelerinin kısaldığı ve nakit kullanımının azaldığı belirtiliyor.'),
                    'tags' => ['İstanbul'],
                ],
            ],
            'dunya' => [
                [
                    'title' => 'G7 Zirvesinde İklim Krizi Masaya Yatırıldı',
                    'slug' => 'g7-zirvesinde-iklim-krizi-masaya-yatirildi',
                    'excerpt' => 'Liderler, 2030 hedeflerini gözden geçirmek için olağanüstü oturum düzenledi.',
                    'content' => $p('G7 ülkeleri, emisyon azaltımı ve yenilenebilir yatırımları hızlandırmak için yeni taahhütler tartıştı.')
                        .$p('Gelişmekte olan ülkelere finansman desteğinin artırılması da gündemin öncelikli maddeleri arasındaydı.'),
                    'breaking' => true,
                    'tags' => ['İklim'],
                ],
                [
                    'title' => 'Avrupa Parlamentosu Dijital Pazar Kurallarını Sıkılaştırıyor',
                    'slug' => 'avrupa-parlamentosu-dijital-pazar-kurallarini-sikilastiriyor',
                    'excerpt' => 'Teknoloji devlerine getirilecek yükümlülükler ve kullanıcı verisi koruma standartları güncelleniyor.',
                    'content' => $p('Taslak metin, uygulama mağazalarında adil rekabet ve şeffaflık ilkelerini güçlendirmeyi amaçlıyor.')
                        .$p('Şirketlerin yaptırım mekanizmalarına itiraz süreçleri de netleştirildi.'),
                    'tags' => ['Yapay zeka'],
                ],
                [
                    'title' => 'Orta Doğu\'da İnsani Yardım Koridorları Genişletildi',
                    'slug' => 'orta-doguda-insani-yardim-koridorlari-genisletildi',
                    'excerpt' => 'Birleşmiş Milletler, kritik bölgelere gıda ve tıbbi malzeme sevkiyatının artırıldığını duyurdu.',
                    'content' => $p('BM yetkilileri, güvenli geçiş noktalarının koordinasyonunun iyileştirildiğini ve sivil halkın temel ihtiyaçlarına erişiminin hızlandığını belirtti.')
                        .$p('Bölgesel aktörlerle yapılan görüşmelerin süreceği kaydedildi.'),
                ],
                [
                    'title' => 'Küresel Ticarette Konteyner Navlunlarında Denge Aranıyor',
                    'slug' => 'kuresel-ticarette-konteyner-navlunlarinda-denge-araniyor',
                    'excerpt' => 'Liman yoğunluğu ve yakıt maliyetleri sonrası navlun oranları yılın ikinci çeyreğinde yatay seyretti.',
                    'content' => $p('Analistler, tedarik zincirlerinin kademeli olarak normale döndüğünü, ancak jeopolitik risklerin fiyatları desteklediğini ifade ediyor.')
                        .$p('Uzun vadede yeşil lojistik yatırımlarının artması bekleniyor.'),
                    'tags' => ['Döviz'],
                ],
                [
                    'title' => 'Asya-Pasifik\'te Uzay İş Birliği Anlaşması İmzalandı',
                    'slug' => 'asya-pasifikte-uzay-is-birligi-anlasmasi-imzalandi',
                    'excerpt' => 'Üç ülke, düşük yörünge uyduları ve bilimsel araştırma paylaşımı için çerçeve anlaşmaya vardı.',
                    'content' => $p('Anlaşma, veri paylaşımı ve ortak görev planlama süreçlerini kapsıyor.')
                        .$p('Uzay çöpü yönetimi ve sürdürülebilir yörünge kullanımı da protokolde yer aldı.'),
                ],
            ],
            'ekonomi' => [
                [
                    'title' => 'Merkez Bankası Faizi Sabit Tuttu, Piyasalar Olumlu Karşıladı',
                    'slug' => 'merkez-bankasi-faizi-sabit-tuttu-piyasalar-olumlu-karsiladi',
                    'excerpt' => 'Para politikası kurulunun kararının ardından borsa yüzde 1,4 yükselişle kapandı.',
                    'content' => $p('Merkez Bankası, politika faizini değiştirmeden sabit tuttuğunu açıkladı. Kurul, enflasyondaki gerilemenin sürdüğünü ve sıkı duruşun korunacağını vurguladı.')
                        .$p('Piyasalar kararı olumlu fiyatladı; tahvil getirilerinde hafif düşüş gözlendi.'),
                    'breaking' => true,
                    'views' => 31700,
                    'tags' => ['Merkez Bankası', 'Döviz'],
                ],
                [
                    'title' => 'Enflasyon Verisi Açıklandı: Nisan Ayında Ne Oldu?',
                    'slug' => 'enflasyon-verisi-aciklandi-nisan-ayinda-ne-oldu',
                    'excerpt' => 'TÜİK verilerine göre yıllık enflasyon beklentilerin altında kaldı; çekirdek göstergeler takip ediliyor.',
                    'content' => $p('Açıklanan rakamlar, gıda ve enerji grubunda baz etkisinin belirginleştiğini gösteriyor.')
                        .$p('Ekonomistler, yılın ikinci yarısında ivmenin yumuşayabileceğini öngörüyor.'),
                    'tags' => ['Merkez Bankası'],
                ],
                [
                    'title' => 'KOBİ\'lere Yönelik Kredi Garanti Limitleri Güncellendi',
                    'slug' => 'kobilere-yonelik-kredi-garanti-limitleri-guncellendi',
                    'excerpt' => 'Yeni düzenleme, yatırım ve ihracat odaklı projelerde kefalet kapsamını genişletiyor.',
                    'content' => $p('KGF kaynaklı destek paketinde sektörel öncelikler yeniden tanımlandı.')
                        .$p('Başvuru süreçlerinin dijitalleştirilmesiyle sürelerin kısalması hedefleniyor.'),
                ],
                [
                    'title' => 'Turizm Gelirleri Önceki Yıla Göre Yükselişte',
                    'slug' => 'turizm-gelirleri-onceki-yila-gore-yukseliste',
                    'excerpt' => 'Kültür ve Turizm Bakanlığı, ilk çeyrek verilerinde güçlü artış olduğunu bildirdi.',
                    'content' => $p('Konaklama ve yeme-içme harcamaları öne çıkarken, bölgesel dağılımda Ege ve Akdeniz ağırlığını korudu.')
                        .$p('Sektör temsilcileri, erken rezervasyon kampanyalarının katkısına işaret etti.'),
                ],
                [
                    'title' => 'Dolar/TL Kurunda Son Durum: Piyasa Beklentileri',
                    'slug' => 'dolar-tl-kurunda-son-durum-piyasa-beklentileri',
                    'excerpt' => 'Kur, gün içinde dar bantta hareket ederken yurt içi veriler takip edildi.',
                    'content' => $p('Analistler, küresel para politikası sinyallerinin kur üzerinde belirleyici olduğunu belirtti.')
                        .$p('Yerel tarafta cari denge ve sermaye girişleri izlenmeye devam ediyor.'),
                    'breaking' => true,
                    'views' => 29100,
                    'tags' => ['Döviz'],
                ],
            ],
            'spor' => [
                [
                    'title' => 'Galatasaray Şampiyonluğa Üç Maç Uzakta: Derbi Öncesi Moral Zirve',
                    'slug' => 'galatasaray-sampiyonluga-uc-mac-uzakta-derbi-oncesi-moral-zirve',
                    'excerpt' => 'Sarı-kırmızılılar, Avrupa\'daki başarısını iç sahaya taşımayı sürdürüyor.',
                    'content' => $p('Teknik ekip, kadro rotasyonunu dengeli kullanırken taraftar desteğinin motivasyonu artırdığı vurgulandı.')
                        .$p('Önümüzdeki hafta oynanacak derbi öncesi sakatlık raporları mercek altında.'),
                    'breaking' => true,
                    'views' => 29100,
                    'tags' => ['Galatasaray'],
                ],
                [
                    'title' => 'Milli Takım Aday Kadrosu Açıklandı: Sürpriz İsimler Listede',
                    'slug' => 'milli-takim-aday-kadrosu-aciklandi-surpriz-isimler-listede',
                    'excerpt' => 'Teknik direktör, hazırlık maçları için 26 kişilik listeyi duyurdu.',
                    'content' => $p('Listede genç yeteneklerin yanı sıra tecrübeli isimler de yer aldı.')
                        .$p('Kamp programı ve rakip analizleri basınla paylaşıldı.'),
                    'tags' => ['Milli takım'],
                ],
                [
                    'title' => 'Basketbol Süper Ligi\'nde Play-off Eşleşmeleri Belli Oldu',
                    'slug' => 'basketbol-super-liginde-play-off-eslesmeleri-belli-oldu',
                    'excerpt' => 'Normal sezon sona ererken üst sıraların puan farkı kritik eşleşmelere yol açtı.',
                    'content' => $p('Serilerin beş maça uzayabileceği format onaylandı.')
                        .$p('Seyirci kapasitesi ve bilet satış tarihleri açıklandı.'),
                ],
                [
                    'title' => 'Voleybol Federasyonu Milli Takım Kamp Tarihlerini Duyurdu',
                    'slug' => 'voleybol-federasyonu-milli-takim-kamp-tarihlerini-duyurdu',
                    'excerpt' => 'A Milli Kadın ve Erkek takımları, uluslararası turnuva öncesi kamplara çıkacak.',
                    'content' => $p('Antrenör kadrosunda yapılan güçlendirmelerin takıma yansıması bekleniyor.')
                        .$p('Hazırlık maçları takvimi federasyon sitesinde yayımlandı.'),
                ],
                [
                    'title' => 'Formula 1\'de İstanbul Park\'ın Gündeme Gelmesi Heyecan Yarattı',
                    'slug' => 'formula-1de-istanbul-parkin-gundeme-gelmesi-heyecan-yaratti',
                    'excerpt' => 'Organizatörler, takvim görüşmelerinin sürdüğünü ve altyapı güncellemelerinin değerlendirildiğini belirtti.',
                    'content' => $p('Taraftarlar, geçmiş yarışların getirdiği izlenimle sosyal medyada yoğun destek verdi.')
                        .$p('Resmi açıklama için tarih verilmedi.'),
                ],
            ],
            'teknoloji' => [
                [
                    'title' => 'Yapay Zeka Yasası Meclis Gündemine Girdi: Düzenleme Ne Getiriyor?',
                    'slug' => 'yapay-zeka-yasasi-meclis-gundemine-girdi-duzenleme-ne-getiriyor',
                    'excerpt' => 'Uzmanlar, taslak metni hem fırsatlar hem riskler açısından değerlendirdi.',
                    'content' => $p('Taslak; risk sınıflandırması, şeffaflık ve insan denetimi ilkelerini öne çıkarıyor.')
                        .$p('Sektör temsilcileri, inovasyonu baltamayacak denge arayışında olduklarını ifade etti.'),
                    'breaking' => true,
                    'tags' => ['Yapay zeka'],
                ],
                [
                    'title' => 'Yerli Elektrikli Araç TOGG\'da Yeni Model Müjdesi',
                    'slug' => 'yerli-elektrikli-arac-toggda-yeni-model-mujdesi',
                    'excerpt' => 'Şirket yetkilileri, 2026 yılında SUV segmentine giriş yapılacağını duyurdu.',
                    'content' => $p('Yeni modelde batarya verimliliği ve yazılım güncellemeleri öne çıkacak.')
                        .$p('Ön sipariş ve teslimat planları önümüzdeki çeyrekte netleşecek.'),
                    'tags' => ['TOGG'],
                ],
                [
                    'title' => 'Siber Güvenlikte Sıfır Güven Mimarisi Yaygınlaşıyor',
                    'slug' => 'siber-guvenlikte-sifir-guven-mimarisi-yayginlasiyor',
                    'excerpt' => 'Kurumsal ağlarda çok faktörlü kimlik doğrulama ve mikro segmentasyon standart hale geliyor.',
                    'content' => $p('Uzmanlar, bulut geçişiyle birlikte politika yönetiminin merkezileştirilmesinin kritik olduğunu vurguluyor.')
                        .$p('Sektörde sertifikasyon programlarına talep arttı.'),
                ],
                [
                    'title' => 'Yapay Zeka Destekli Tıbbi Görüntüleme Çalışmalarında İlerleme',
                    'slug' => 'yapay-zeka-destekli-tibbi-goruntuleme-calismalarinda-ilerleme',
                    'excerpt' => 'Araştırmacılar, erken teşhis modellerinin klinik testlerinde yüksek doğruluk elde ettiğini bildirdi.',
                    'content' => $p('Etik kurul süreçleri ve veri anonimleştirme standartları titizlikle uygulanıyor.')
                        .$p('Hastanelerle pilot uygulamalar genişletilecek.'),
                    'tags' => ['Yapay zeka'],
                ],
                [
                    'title' => 'Türkiye\'de Bulut Bilişim Yatırımları Hız Kesmiyor',
                    'slug' => 'turkiyede-bulut-bilisim-yatirimlari-hiz-kesmiyor',
                    'excerpt' => 'Veri merkezi kapasitesi ve yerelleştirilmiş hizmet seçenekleri rekabeti artırıyor.',
                    'content' => $p('KOBİ\'lerin SaaS çözümlerine geçişi, pazarın büyümesinde belirleyici oldu.')
                        .$p('Enerji verimliliği ve soğutma teknolojileri yatırım gündeminde.'),
                ],
            ],
            'kultur' => [
                [
                    'title' => 'İstanbul Film Festivali Perde Açıyor: Bu Yılın Öne Çıkanları',
                    'slug' => 'istanbul-film-festivali-perde-aciyor-bu-yilin-one-cikanlari',
                    'excerpt' => '44. festival, 60 ülkeden 200\'den fazla filmi sanatseverlerle buluşturacak.',
                    'content' => $p('Açılış filmi ve özel gösterimler için biletler kısa sürede tükendi.')
                        .$p('Yönetmen söyleşileri ve atölyeler çevrim içi erişime de açılacak.'),
                    'tags' => ['Film festivali', 'İstanbul'],
                ],
                [
                    'title' => 'Arkeolojik Kazılarda Anadolu\'ya Özgü Yeni Buluntular',
                    'slug' => 'arkeolojik-kazilarda-anadoluya-ozgu-yeni-buluntular',
                    'excerpt' => 'Seramik parçaları ve yazıtlar, bölgedeki ticaret ağları hakkında yeni ipuçları sunuyor.',
                    'content' => $p('Bilim insanları, buluntuların tarihlendirmesini laboratuvar analizleriyle sürdürüyor.')
                        .$p('Müze envanterine kazı alanından parça aktarımı planlandı.'),
                ],
                [
                    'title' => 'Opera Sahnesinde Genç Yetenekler Öne Çıkıyor',
                    'slug' => 'opera-sahnesinde-genc-yetenekler-one-cikiyor',
                    'excerpt' => 'Devlet opera ve balesi, sezon programında çağdaş eserlere daha fazla yer vereceğini duyurdu.',
                    'content' => $p('Koreografi ve orkestra şefliğinde uluslararası iş birlikleri artırıldı.')
                        .$p('Bilet satışlarında öğrenci indirimleri genişletildi.'),
                ],
                [
                    'title' => 'Kitap Fuarında İmza Günleri Yoğun İlgi Gördü',
                    'slug' => 'kitap-fuarinda-imza-gunleri-yogun-ilgi-gordu',
                    'excerpt' => 'Yazarlar okurlarıyla buluşurken çocuk kitapları bölümü rekor ziyaretçi sayısına ulaştı.',
                    'content' => $p('Yayınevleri, sesli kitap ve dijital lisans anlaşmalarında artış bildirdi.')
                        .$p('Fuar alanında söyleşi ve şiir dinletileri devam ediyor.'),
                ],
                [
                    'title' => 'Müzik Festivallerinde Sürdürülebilirlik Adımları',
                    'slug' => 'muzik-festivallerinde-surdurulebilirlik-adimlari',
                    'excerpt' => 'Organizatörler, tek kullanımlık plastikleri azaltmak için geri dönüşüm noktalarını çoğalttı.',
                    'content' => $p('Enerji tüketimini düşürmek için güneş panelli sahne çözümleri pilot olarak denendi.')
                        .$p('Katılımcılar, ulaşımda toplu taşıma teşvikinden memnuniyet bildirdi.'),
                ],
            ],
        ];
    }
}
