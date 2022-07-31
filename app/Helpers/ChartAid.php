<?php

namespace App\Helpers;

use App\Enums\Frequency;
use App\Enums\Period;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

class ChartAid
{
    /**
     * @var mixed|string
     */
    private Frequency $frequency;
    private Period $period;
    private Collection $models;
    private string $aggregateType;
    private ?string $aggregateColumn;
    private bool $showFuture = false;

    public function __construct(Period $period, Frequency $frequency, string $aggregateType = 'count', ?string $aggregateColumn = null)
    {
        $this->period = $period;
        $this->frequency = $frequency;
        $this->aggregateType = $aggregateType;
        $this->aggregateColumn = $aggregateColumn;
    }


    /**
     * @param Collection $models
     * @param null       $frequencyCount
     * @return array
     */
    #[ArrayShape(['labels' => "array", 'datasets' => "array"])]
    public function chartDataSet(Collection $models, $frequencyCount = null): array
    {
        $this->models = $models;

        if(is_null($frequencyCount)) {
            $frequencyCount = match ($this->period) {
                Period::TODAY => 24,
                Period::LAST_SEVEN_DAYS => 7,
                Period::LAST_THIRTY_DAYS => match ($this->frequency) {
                    Frequency::WEEKLY => now()->subDays(30)->diffInWeeks(),
                    default => 30,
                },
                Period::LAST_THREE_MONTHS => match ($this->frequency) {
                    Frequency::MONTHLY => 3,
                    default => now()->subMonths(3)->diffInWeeks(),
                },
                Period::LAST_SIX_MONTHS => 6,
                Period::YTD => match ($this->frequency) {
                    Frequency::QUARTERLY => 4,
                    default => 13,
                },
            };
        }

        $date = new Carbon;

        $data = collect();
        for($i = 0; $i < $frequencyCount; $i++) {
            $dateString = self::chartDateFormat($date);

            $data[$dateString] = $this->aggregate($dateString);

            switch($this->frequency->value) {
                case 'hourly':
                    $date->subHour();
                    break;
                case 'daily':
                    $date->subDay();
                    break;
                case 'weekly':
                    $date->subWeek();
                    break;
                case 'quarterly':
                    $date->subMonths(3);
                    break;
                case 'yearly':
                case 'monthly':
                    $date->subMonth();
                    break;
                default:
                    $date->subDay();
            }
        }

//        dd($data);

        if($this->showFuture) {
            $data = $data->sortKeys();
        } else {
            $data = $data->reverse();
        }

        foreach($data as $key => $value) {
            $date = self::parseCarbonDate($key);

            $name = self::getLabelName($date);

            $data[$name] = $value;
            $data->forget($key);
        }

        return [
            'labels'   => $data->keys()->toArray(),
            'datasets' => $data->values()->toArray()
        ];
    }

    public function aggregate($dateString): int
    {
        return match ($this->aggregateType) {
            'sum' => isset($this->models[$dateString])
                ? $this->models[$dateString]->sum($this->aggregateColumn)
                : 0,
            default => isset($this->models[$dateString])
                ? $this->models[$dateString]->count()
                : 0,
        };
    }

    public function parseCarbonDate($time): Carbon|CarbonImmutable
    {
        return match ($this->frequency) {
            Frequency::YEARLY => Carbon::createFromDate($time, tz: 'Africa/Nairobi'),
            Frequency::WEEKLY => Carbon::now()->setISODate(now()->year, $time),
            Frequency::DAILY => Carbon::createFromDate(day: $time, tz: 'Africa/Nairobi'),
            Frequency::HOURLY => Carbon::createFromTime($time, tz: 'Africa/Nairobi'),
            default => Carbon::parse($time, 'Africa/Nairobi')
        };
    }

    public function getLabelName(Carbon|CarbonImmutable $date): int|string
    {
        $freq = $this->frequency->value;

        if($freq === 'yearly') {
            if($date->isCurrentYear()) {
                $name = 'This year';
            } else if($date->isLastYear()) {
                $name = 'Last year';
            } else {
                $name = $date->year;
            }
        } else if($freq === 'quarterly') {
            $endDate = $date->isCurrentMonth()
                ? "Current Month"
                : $date->shortMonthName;
            $startDate = $date->subMonths(2)->shortMonthName;

            $name = "$startDate - $endDate";
        } else if($freq === 'monthly') {
            if($date->isCurrentMonth()) {
                $name = 'This month';
            } else if($date->isLastMonth()) {
                $name = 'Last month';
            } else {
                $name = $date->shortMonthName;
            }
        } else if($freq === 'weekly') {
            if($date->isCurrentWeek()) {
                $name = 'This week';
            } else if($date->isLastWeek()) {
                $name = 'Last week';
            } else {
                $name = "{$date->diffInWeeks()} week" . ($date->diffInWeeks() > 1
                        ? 's'
                        : '') . " ago";
            }
        } else if($freq === 'daily') {
            if($date->isCurrentDay()) {
                $name = 'Today';
            } else if($date->isYesterday()) {
                $name = 'Yesterday';
            } else {
                $name = $this->period === Period::LAST_SEVEN_DAYS
                    ? $date->shortDayName
                    : $date->format('dS');
            }
        } else {
            if($date->isCurrentHour()) {
                $name = 'Current hour';
            } else if($date->isLastHour()) {
                $name = 'Last hour';
            } else {
                $name = $date->format('Hi \h\r\s');
            }
        }

        return $name;
    }

    public function setShowFuture(bool $showFuture): void
    {
        $this->showFuture = $showFuture;
    }


    /** chart   */
    public function chartDateFormat($date): string
    {
        $carbonDate = Carbon::parse($date)->timezone('Africa/Nairobi');

        return match ($this->frequency) {
            Frequency::YEARLY => $carbonDate->format('Y'),
            Frequency::QUARTERLY, Frequency::MONTHLY => $carbonDate->format('Y-m'),
            Frequency::WEEKLY => $carbonDate->format('W'),
            Frequency::DAILY => $carbonDate->format('d'),
            Frequency::HOURLY => $carbonDate->format('H'),
            default => $carbonDate->toDateString()
        };
    }

    public function chartStartDate(): Carbon|CarbonImmutable|\Carbon\Carbon
    {
        $carbonDate = \LocalCarbon::now();

        return match ($this->period) {
            Period::TODAY => $carbonDate->subDay(),
            Period::LAST_SEVEN_DAYS => $carbonDate->subWeek(),
            Period::LAST_THIRTY_DAYS => $carbonDate->subDays(30),
            Period::LAST_THREE_MONTHS => $carbonDate->subMonths(3),
            Period::LAST_SIX_MONTHS => $carbonDate->subMonths(6),
            Period::YTD => $carbonDate->subYear(),
        };
    }
}
