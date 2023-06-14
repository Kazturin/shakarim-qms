<?php

namespace App\Filament\Pages;

use App\Events\TestEvent;
use App\Events\TicketCreatedEvent;
use App\Models\OperatorService;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Pages\Page;
// use Livewire\WithPagination;

class TakeTickets extends Page
{

  //  use WithPagination;
   // protected static ?string $title = 'Custom Page Title';

    public Ticket|null $ticket = null;

    public $tickets;

    public $ticketsCount = 0;

    public $allowServicesIds = [];

    public bool $invitation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.take-tickets';

    protected $listeners = [
        'recordUpdated',
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        $user = User::find(auth()->user()->id);
        if ($user && $user->hasRole('Operator')){
            return true;
        };
        return false;
    }



   // protected $listeners = ['refreshComponent' => '$refresh'];

    protected static function getNavigationLabel(): string
    {
        return 'Талондар қабылдау';
    }

    protected function getHeading(): string
    {
        return 'Талон';
    }

    public function mount(){
        $this->allowServicesIds = OperatorService::where('operator_id',auth()->user()->id)->pluck('service_id');
        $this->test = 'tets';
        $this->tickets = Ticket::with('service')
            ->whereIn('service_id',$this->allowServicesIds)
            ->where('status',Ticket::STATUS_WAITING)
            ->whereDate('created_at',Carbon::today())
            ->orderBy('id')
            ->limit(20)
            ->get();
     //   dd($this->tickets);
        $this->ticketsCount = Ticket::where('operator_id',auth()->user()->id)->count();
    }

    public function getTicket(){

       // dd($this->test);
        if ($this->tickets && count($this->tickets)>0){
            $this->ticket = $this->tickets[0];
            $this->ticket->status = Ticket::STATUS_IN_PROGRESS;
            $this->ticket->operator_id = auth()->user()->id;
            $this->ticket->save();
            $this->invitation = true;
            $this->tickets = Ticket::with('service')->whereIn('service_id',$this->allowServicesIds)->where('status',Ticket::STATUS_WAITING)->orderBy('id')->limit(20)->get();
         //   event(new TestEvent());
            $this->ticketsCount = Ticket::where('operator_id',auth()->user()->id)->count();
            event(new TestEvent());
        }else{
            Filament::notify('warning', 'Кезек бос');
        }
    }

    public function closeTicket(){
        $this->ticket = null;
        $this->invitation = false;
       // $this->emit('refreshComponent');
     //   $this->tickets = Ticket::whereIn('service_id',$this->allowServicesIds)->where('status',Ticket::STATUS_WAITING)->orderBy('id')->get();
    }

    public function recordUpdated(){
        $this->tickets = Ticket::with('service')
            ->whereIn('service_id',$this->allowServicesIds)
            ->where('status',Ticket::STATUS_WAITING)
            ->whereDate('created_at',Carbon::today())
            ->orderBy('id')
            ->limit(20)
            ->get();
    }
}