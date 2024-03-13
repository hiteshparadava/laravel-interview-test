<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Prize;
use App\Http\Requests\PrizeRequest;
use Illuminate\Http\Request;
use DB;



class PrizesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $prizes = Prize::all();
        $titles = Prize::select(DB::raw("CONCAT(COALESCE(`title`,''),' (',COALESCE(`probability`,''),'%)') AS lable"))
            ->pluck('lable')
            ->all();
        $probability=Prize::pluck('probability')->all();


        $titles_for_actual_rewards = Prize::select(DB::raw("CONCAT(COALESCE(`title`,''),' (',COALESCE(`actual_probability`,''),'%)') AS lable"))
            ->pluck('lable')
            ->all();
        $awarded=Prize::pluck('awarded')->all();

        

        return view('prizes.index', ['prizes' => $prizes,'titles'=>$titles,'probability'=>$probability,'awarded'=>$awarded,'titles_for_actual_rewards'=>$titles_for_actual_rewards]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('prizes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PrizeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PrizeRequest $request)
    {
        $prize = new Prize;
        $prize->title = $request->input('title');
        $prize->probability = floatval($request->input('probability'));
        $prize->save();

        return to_route('prizes.index');
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $prize = Prize::findOrFail($id);
        return view('prizes.edit', ['prize' => $prize]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PrizeRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PrizeRequest $request, $id)
    {
        $prize = Prize::findOrFail($id);
        $prize->title = $request->input('title');
        $prize->probability = floatval($request->input('probability'));
        $prize->save();

        return to_route('prizes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $prize = Prize::findOrFail($id);
        $prize->delete();

        return to_route('prizes.index');
    }


    public function simulate(Request $request)
    {
        $prizes = Prize::inRandomOrder()->get();
        $total_awarded = $prizes->sum('awarded');
        $total_prizes = $prizes->count();

        $point_status=0;
        $awarded_prize_count=0;
        $i=1;
        foreach($prizes as $prize)
        {
            $awarded_prize=round(($request->number_of_prizes*$prize->probability)/100, 2);
            if (strpos($awarded_prize,'.') !== false) {
                if($point_status==0)
                {
                    $awarded_prize=ceil($awarded_prize);
                    $point_status=1;
                }
                else
                {
                    $awarded_prize=floor($awarded_prize);
                    $point_status=0;
                }
                $awarded_prize_count=$awarded_prize_count+$awarded_prize;

                if($i==4 && $awarded_prize_count<$request->number_of_prizes)
                {
                    $awarded_prize=ceil($awarded_prize);
                }
                else
                {
                    $awarded_prize=floor($awarded_prize);
                }
                
            }
            $new_awarded_prize=$awarded_prize+$prize->awarded;
            $actual_probability=round(((100*$new_awarded_prize)/($total_awarded+$request->number_of_prizes)),2);
            Prize::where('id',$prize->id)->update(['awarded'=>$new_awarded_prize,'actual_probability'=>$actual_probability]);
            $i++;
        }
        // for ($i = 0; $i < $request->number_of_prizes ?? 10; $i++) {
        //     Prize::nextPrize();
        // }

        return to_route('prizes.index');
    }

    public function reset()
    {
        Prize::query()->update(['awarded'=>0,'actual_probability'=>0]);
        return to_route('prizes.index');
    }
}
