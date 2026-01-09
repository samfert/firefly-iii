<?php

declare(strict_types=1);

namespace FireflyIII\Events\Model\Bill;

use FireflyIII\Events\Event;
use FireflyIII\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class WarnUserAboutOverdueSubscriptions
 *
 * Evento para avisar usuario sobre assinaturas vencidas.
 */
class WarnUserAboutOverdueSubscriptions extends Event
{
    use SerializesModels;

    public function __construct(public User $user, public array $overdue) {}

}
