<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Idées d\'activités manuelles à faire chez soi pour se détendre',
                'excerpt' => 'La créativité comme refuge contre le stress. Dans un quotidien marqué par la vitesse, les écrans et les obligations, beaucoup ressentent le besoin de se recentrer.',
                'content' => $this->getArticle1Content(),
                'image_url' => '/images/article_image.png',
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Pourquoi choisir une box surprise DIY ?',
                'excerpt' => 'L\'effet de surprise : un booster de créativité. Recevoir une box surprise, c\'est recréer la magie des cadeaux.',
                'content' => $this->getArticle2Content(),
                'image_url' => '/images/article_image.png',
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Pourquoi choisir une box créative écoresponsable ?',
                'excerpt' => 'Une consommation alignée avec ses valeurs. Les consommatrices d\'aujourd\'hui ne veulent plus acheter pour acheter.',
                'content' => $this->getArticle3Content(),
                'image_url' => '/images/article_image.png',
                'published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('articles')->insert($articles);
    }

    private function getArticle1Content(): string
    {
        return '
<h2>La créativité comme refuge contre le stress</h2>
<p>Dans un quotidien marqué par la vitesse, les écrans et les obligations, beaucoup ressentent le besoin de se recentrer. Les activités manuelles à faire chez soi apparaissent comme une solution simple et accessible pour déconnecter, s\'épanouir et se sentir mieux. Loomi, avec ses box créatives, propose une nouvelle façon de prendre soin de soi tout en explorant sa créativité.</p>

<h2>Une bougie faite maison pour allumer son cocon</h2>
<p>Fabriquer ses bougies est une activité apaisante et accessible à tous. Les box Loomi fournissent tout le matériel nécessaire : cire naturelle, mèches, pots, huiles essentielles... Allumer une bougie que l\'on a créé soi-même procure un sentiment unique. Ce geste transforme l\'intérieur en sanctuaire de bien-être, où la lumière douce apaise l\'esprit.</p>

<h2>Le punch needle : entre création textile et méditation</h2>
<p>Cette technique de broderie moderne permet de créer rapidement des motifs en relief. Elle est intuitive, relaxante et ouvre la voie à de nombreuses décorations : coussins, cadres muraux, accessoires. Les kits créatifs fournis par Loomi rendent cette pratique accessible, même aux débutants, grâce à un guide pas à pas.</p>

<h2>Savon naturel maison : un soin pour la peau et l\'esprit</h2>
<p>La fabrication de savons naturels est une activité utile, écologique et créative. Les box Loomi prévoient des moules, des bases végétales, des colorants et parfums naturels. L\'aspect sensoriel de la préparation, combiné à la satisfaction d\'utiliser un produit fait main, en fait une expérience à la fois bien-être et engagée.</p>

<h2>Organiseur mural en bois : allier pratique et esthétique</h2>
<p>Pour celles et ceux qui aiment bricoler en douceur, la personnalisation d\'un organiseur mural est idéale. Loomi propose dans ses box des planches prédécoupées, de la peinture, de la ficelle, et des crochets. Ce projet vous permet d\'embellir votre intérieur tout en développant vos compétences manuelles.</p>

<h2>Carnet de gratitude : la créativité comme outil d\'introspection</h2>
<p>Confectionner un carnet de gratitude, c\'est se donner un espace pour noter ce qui nous fait du bien. Loomi conçoit des kits avec papiers recyclés, reliure artisanale, stickers, et illustrations inspirantes. Une façon douce d\'intégrer un rituel positif dans son quotidien.</p>

<h2>Une invitation à ralentir avec Loomi</h2>
<p>Les box créatives Loomi sont plus qu\'un loisir : elles sont une véritable expérience de reconnexion à soi. En proposant des activités manuelles simples, utiles et écoresponsables, Loomi transforme le temps libre en moment précieux de création.</p>
        ';
    }

    private function getArticle2Content(): string
    {
        return '
<h2>L\'effet de surprise : un booster de créativité</h2>
<p>Recevoir une box surprise, c\'est recréer la magie des cadeaux. Chaque mois, Loomi glisse dans votre boîte aux lettres une nouvelle activité manuelle inédite. Vous n\'avez rien à prévoir, tout est prêt. Le thème est une surprise, mais le plaisir est garanti.</p>

<h2>Redécouvrir le plaisir de ne pas choisir</h2>
<p>Le monde moderne nous expose à une infinité de choix qui peuvent créer de la fatigue décisionnelle. La box surprise Loomi permet de lâcher prise, de se laisser guider vers des activités nouvelles, auxquelles on n\'aurait pas pensé spontanément. C\'est une invitation à sortir de sa zone de confort avec bienveillance.</p>

<h2>Un format pensé pour le bien-être</h2>
<p>L\'abonnement mensuel Loomi inclut une activité manuelle relaxante, conçue pour être accessible, plaisante et réalisable en moins d\'une heure. C\'est un moment de pause, loin des écrans, idéal pour recharger les batteries et cultiver la pleine conscience.</p>

<h2>Une idée cadeau pleine de sens</h2>
<p>Offrir une idée cadeau DIY n\'est pas simplement un objet, mais une expérience. Que ce soit pour un anniversaire, Noël ou un départ en congé, la box surprise Loomi apporte à la fois créativité, bien-être et personnalisation. La carte cadeau permet d\'adapter l\'abonnement selon les envies.</p>

<h2>Une approche inclusive et accessible</h2>
<p>Que vous soyez novice ou passionné de DIY, chaque box est conçue pour être réalisée sans connaissances préalables. Le matériel est inclus, les instructions sont claires, et l\'expérience est valorisante. L\'objectif n\'est pas la performance, mais le plaisir de créer.</p>

<h2>L\'originalité au rendez-vous chaque mois</h2>
<p>Loomi renouvelle sans cesse ses activités : création de bijoux, cosmétiques naturels, déco, objets utiles... La box surprise devient une porte d\'entrée vers une multitude de passions, toutes testées pour être simples, durables et gratifiantes.</p>

<h2>Se faire plaisir en toute liberté</h2>
<p>Les formules sans engagement permettent d\'essayer en toute sérénité. Vous recevez votre box, vous testez, vous partagez vos réalisations avec la communauté Loomi. Une façon légère et inspirante d\'ajouter une touche de créativité à votre quotidien.</p>
        ';
    }

    private function getArticle3Content(): string
    {
        return '
<h2>Une consommation alignée avec ses valeurs</h2>
<p>Les consommatrices d\'aujourd\'hui ne veulent plus acheter pour acheter. Elles veulent donner du sens à leurs choix, soutenir des marques engagées et préserver leur santé et l\'environnement. Loomi, en proposant une box créative écoresponsable, répond à cette quête de cohérence.</p>

<h2>Des matériaux naturels et recyclables</h2>
<p>Les box Loomi intègrent des produits choisis avec soin : bois issu de forêts gérées durablement, papiers recyclés, tissus biologiques, colles sans solvants... Le tout est présenté dans un emballage minimaliste et recyclable. L\'expérience créative est respectueuse de la planète.</p>

<h2>Créer pour durer</h2>
<p>Chaque activité propose la création d\'un objet utile ou décoratif. Contrairement à des loisirs jetables, les réalisations Loomi sont faites pour durer. On fabrique un savon pour soi, un objet pour chez soi, une déco qu\'on garde. Moins de gaspillage, plus de fierté.</p>

<h2>Redonner de la valeur au temps libre</h2>
<p>Loomi remet au goût du jour la création artisanale, lente et personnelle. Les activités manuelles ne sont pas qu\'un passe-temps, elles deviennent un moyen de ralentir, de se concentrer et de nourrir son bien-être. Créer pour soi est un acte fort.</p>

<h2>Une communauté créative et inspirante</h2>
<p>Les abonnés Loomi partagent leurs créations sur les réseaux sociaux. Le hashtag #LoomiBox met en avant les talents, donne des idées, crée du lien. Cette preuve sociale renforce la confiance, inspire les nouvelles venues et valorise chaque personne.</p>

<h2>Pour un quotidien plus doux et responsable</h2>
<p>Opter pour une box créative adulte écoresponsable, c\'est s\'offrir une pause mensuelle qui a du sens. On découvre, on apprend, on crée, on partage. C\'est une consommation bienveillante, positive et durable. Loomi n\'est pas qu\'une box, c\'est une philosophie de vie.</p>

<h2>Conclusion : choisir la créativité utile et engagée</h2>
<p>Loomi incarne une nouvelle façon de consommer les loisirs. Accessible, éthique, inspirante, sa box créative écoresponsable permet de concilier plaisir personnel et impact positif. Et si créer devenait votre nouveau geste pour la planète et votre bien-être ?</p>
        ';
    }
}
