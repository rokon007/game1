<?php

namespace App\Livewire\Backend\Lottery;

use Livewire\Component;
use App\Models\Lottery;
use App\Models\LotteryPrize;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EditLottery extends Component
{
    public $lottery;
    public $name = '';
    public $price = '';
    public $draw_date = '';
    public $draw_time = '';
    public $auto_draw = true;

    public $prizes = [];
    public $preSelectedWinners = [];

    public $enablePreSelection = false;
    public $preSelectedTickets = [];

    // protected $rules = [
    //     'name' => 'required|string|max:255',
    //     'price' => 'required|numeric|min:1',
    //     'draw_date' => 'required|date|after:today',
    //     'draw_time' => 'required',
    //     'prizes.*.position' => 'required|string',
    //     'prizes.*.amount' => 'required|numeric|min:1',
    //     'prizes.*.rank' => 'required|integer|min:1',
    //     'preSelectedTickets.*.prize_position' => 'required_if:enablePreSelection,true|string',
    //     'preSelectedTickets.*.ticket_number' => 'required_if:enablePreSelection,true|string|size:8',
    // ];

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'draw_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $today = Carbon::today()->toDateString();
                    $inputDate = Carbon::parse($value)->toDateString();

                    if ($inputDate < $today) {
                        $drawDateTime = Carbon::parse($value . ' ' . $this->draw_time);
                        if ($drawDateTime->greaterThan(now())) {
                            $fail('গতকাল বা তার আগের তারিখের জন্য সময় বর্তমান সময়ের চেয়ে বেশি হতে পারবে না।');
                        }
                    }
                },
            ],
            'draw_time' => 'required|date_format:H:i:s',
            'prizes.*.position' => 'required|string',
            'prizes.*.amount' => 'required|numeric|min:1',
            'prizes.*.rank' => 'required|integer|min:1',
            'preSelectedTickets.*.prize_position' => 'required_if:enablePreSelection,true|string',
            'preSelectedTickets.*.ticket_number' => 'required_if:enablePreSelection,true|string|size:8',
        ];
    }

    public function mount(Lottery $lottery)
    {
        $this->lottery = $lottery;
        $this->name = $lottery->name;
        $this->price = $lottery->price;

        $drawDateTime = explode(' ', $lottery->draw_date);
        $this->draw_date = $drawDateTime[0];
        $this->draw_time = $drawDateTime[1] ?? '00:00';

        $this->auto_draw = $lottery->auto_draw;
        $this->enablePreSelection = !empty($lottery->pre_selected_winners);

        // Load prizes
        $this->prizes = $lottery->prizes->map(function($prize) {
            return [
                'id' => $prize->id,
                'position' => $prize->position,
                'amount' => $prize->amount,
                'rank' => $prize->rank
            ];
        })->toArray();

        // Load pre-selected tickets if they exist
        if (!empty($lottery->pre_selected_winners)) {
            foreach ($lottery->pre_selected_winners as $position => $ticketNumber) {
                $this->preSelectedTickets[] = [
                    'prize_position' => $position,
                    'ticket_number' => $ticketNumber
                ];
            }
        } else {
            $this->addPrize(); // Add at least one prize if none exist
        }
    }

    public function addPrize()
    {
        $this->prizes[] = [
            'id' => null,
            'position' => '',
            'amount' => '',
            'rank' => count($this->prizes) + 1
        ];
    }

    public function addPreSelectedTicket()
    {
        $this->preSelectedTickets[] = [
            'prize_position' => '',
            'ticket_number' => ''
        ];
    }

    public function removePrize($index)
    {
        $prize = $this->prizes[$index];

        if (!is_null($prize['id'])) {
            LotteryPrize::where('id', $prize['id'])->delete();
        }

        unset($this->prizes[$index]);
        $this->prizes = array_values($this->prizes);

        // Re-index ranks
        foreach ($this->prizes as $key => $prize) {
            $this->prizes[$key]['rank'] = $key + 1;
        }
    }

    public function removePreSelectedTicket($index)
    {
        unset($this->preSelectedTickets[$index]);
        $this->preSelectedTickets = array_values($this->preSelectedTickets);
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            $drawDateTime = $this->draw_date . ' ' . $this->draw_time;

            $preSelectedWinners = [];
            if ($this->enablePreSelection && !empty($this->preSelectedTickets)) {
                foreach ($this->preSelectedTickets as $ticket) {
                    if (!empty($ticket['prize_position']) && !empty($ticket['ticket_number'])) {
                        $preSelectedWinners[$ticket['prize_position']] = $ticket['ticket_number'];
                    }
                }
            }

            $this->lottery->update([
                'name' => $this->name,
                'price' => $this->price,
                'draw_date' => $drawDateTime,
                'auto_draw' => $this->auto_draw,
                'pre_selected_winners' => !empty($preSelectedWinners) ? $preSelectedWinners : null
            ]);

            // First delete all prizes not in the current list
            $currentPrizeIds = collect($this->prizes)->pluck('id')->filter()->toArray();
            $this->lottery->prizes()->whereNotIn('id', $currentPrizeIds)->delete();

            // Update or create prizes
            foreach ($this->prizes as $prize) {
                if ($prize['id']) {
                    LotteryPrize::where('id', $prize['id'])->update([
                        'position' => $prize['position'],
                        'amount' => $prize['amount'],
                        'rank' => $prize['rank']
                    ]);
                } else {
                    LotteryPrize::create([
                        'lottery_id' => $this->lottery->id,
                        'position' => $prize['position'],
                        'amount' => $prize['amount'],
                        'rank' => $prize['rank']
                    ]);
                }
            }
        });

        session()->flash('success', 'লটারি সফলভাবে আপডেট হয়েছে!');
        return redirect()->route('admin.lottery.index');
    }

    public function render()
    {
        return view('livewire.backend.lottery.edit-lottery')->layout('livewire.backend.base');
    }
}
