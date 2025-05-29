<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionDelivery;
use App\Models\Box;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessSubscriptionDeliveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-deliveries {--dry-run : Run without actually creating deliveries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and create subscription deliveries for active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Mode DRY RUN activé - Aucune livraison ne sera créée');
        }
        // Récupérer toutes les souscriptions actives
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['order.user', 'type', 'deliveries'])
            ->get();

        $this->info("Nombre d'abonnements actifs trouvés: " . $activeSubscriptions->count());

        $deliveriesCreated = 0;
        $errors = 0;

        foreach ($activeSubscriptions as $subscription) {
            try {
                $result = $this->processSubscriptionDelivery($subscription, $isDryRun);
                if ($result) {
                    $deliveriesCreated++;
                    $this->info("✓ Livraison créée pour l'abonnement #{$subscription->id} (utilisateur: {$subscription->order->user->email})");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Erreur pour l'abonnement #{$subscription->id}: " . $e->getMessage());
            }
        }

        $this->info("\n=== RÉSUMÉ ===");
        $this->info("Livraisons créées: $deliveriesCreated");
        $this->info("Erreurs: $errors");

        return Command::SUCCESS;
    }

    /**
     * Traite une livraison pour un abonnement donné
     */
    private function processSubscriptionDelivery(Subscription $subscription, bool $isDryRun = false): bool
    {
        // Calculer la prochaine date de livraison basée sur la fréquence
        $lastDelivery = SubscriptionDelivery::where('subscription_id', $subscription->id)
            ->orderBy('delivered_at', 'desc')
            ->first();

        $nextDeliveryDate = $this->calculateNextDeliveryDate($subscription, $lastDelivery);

        // Vérifier si une livraison est due
        if ($nextDeliveryDate->isFuture()) {
            return false; // Pas encore temps pour la prochaine livraison
        }

        // Sélectionner une boîte appropriée pour cette livraison
        $box = $this->selectBoxForSubscription($subscription);

        if (!$box) {
            throw new \Exception("Aucune boîte disponible pour l'abonnement #{$subscription->id}");
        }

        if ($isDryRun) {
            $this->line("  → Livraison prévue: Boîte '{$box->name}' pour le {$nextDeliveryDate->format('d/m/Y')}");
            return true;
        }

        // Créer la livraison
        SubscriptionDelivery::create([
            'subscription_id' => $subscription->id,
            'box_id' => $box->id,
            'delivered_at' => $nextDeliveryDate,
        ]);

        return true;
    }

    /**
     * Calcule la prochaine date de livraison
     */
    private function calculateNextDeliveryDate(Subscription $subscription, ?SubscriptionDelivery $lastDelivery): Carbon
    {
        if (!$lastDelivery) {
            // Première livraison : utiliser la date de début + 1 mois
            return Carbon::parse($subscription->start_date)->addMonth();
        }

        // Livraisons suivantes : ajouter la fréquence à la dernière livraison
        $frequency = $subscription->frequency ?? 'monthly'; // Par défaut mensuel

        switch ($frequency) {
            case 'weekly':
                return Carbon::parse($lastDelivery->delivered_at)->addWeek();
            case 'biweekly':
                return Carbon::parse($lastDelivery->delivered_at)->addWeeks(2);
            case 'monthly':
            default:
                return Carbon::parse($lastDelivery->delivered_at)->addMonth();
            case 'quarterly':
                return Carbon::parse($lastDelivery->delivered_at)->addMonths(3);
        }
    }

    /**
     * Sélectionne une boîte appropriée pour l'abonnement
     */
    private function selectBoxForSubscription(Subscription $subscription): ?Box
    {
        // Récupérer les boîtes déjà envoyées pour cet abonnement
        $deliveredBoxIds = SubscriptionDelivery::where('subscription_id', $subscription->id)
            ->pluck('box_id')
            ->toArray();

        // Sélectionner une boîte non encore envoyée
        // Pour l'instant, on prend une boîte aléatoire disponible
        // Vous pouvez adapter cette logique selon vos besoins (catégorie, préférences, etc.)
        $availableBox = Box::whereNotIn('id', $deliveredBoxIds)
            ->where('active', true) // Supposant qu'il y a un champ active
            ->inRandomOrder()
            ->first();

        // Si toutes les boîtes ont été envoyées, recommencer avec toutes les boîtes
        if (!$availableBox) {
            $availableBox = Box::where('active', true)
                ->inRandomOrder()
                ->first();
        }

        return $availableBox;
    }
}
