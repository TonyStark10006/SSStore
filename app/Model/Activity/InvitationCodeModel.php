<?php

namespace App\Model\Activity;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationCodeModel extends Model
{
    //
    private $invCode;
    private $invCodeTimes;

    use filterTrait;

    public function addInvCode(Request $request)
    {
        //
        $this->invCode = self::filter($request->input('invCode'));
        $this->invCodeTimes = filter_var($request->input('invCodeTimes'), FILTER_SANITIZE_NUMBER_INT);

        if (empty($this->invCode) || empty($this->invCodeTimes)) {
            return false;
        }

        $result = DB::table('inv_code')->insert([
            'inv_code' => $this->invCode,
            'valid_times' => $this->invCodeTimes
        ]);

        if ($result) {
            return array(
                'invCode' => $this->invCode,
                'invCodeTimes' => $this->invCodeTimes
            );
        } else {
            return false;
        }
    }
}
