<?php
declare(strict_types = 1);

//
//  MainSchedule.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('default')]
final readonly class MainSchedule implements ScheduleProviderInterface {
    public function __construct(
        private CacheInterface $cache
    ) {}

    /**
     * @return Schedule
     */
    public function getSchedule(): Schedule {
        $schedule = new Schedule();
        $schedule->add(message: RecurringMessage::cron(expression: '#midnight', message: new RunCommandMessage('cappuccino:sync')));
        $schedule->stateful(state: $this->cache);
        $schedule->processOnlyLastMissedRun(onlyLastMissed: true);
        return $schedule;
    }
}
