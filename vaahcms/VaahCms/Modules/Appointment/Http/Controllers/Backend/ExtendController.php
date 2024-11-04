<?php  namespace VaahCms\Modules\Appointment\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExtendController extends Controller
{

    public static $base;
    public static $link;

    //----------------------------------------------------------
    public function __construct()
    {
        $base_url = route('vh.backend.appointment')."#/";
        $link = $base_url;

        self::$base = $base_url;
        self::$link = $link;
    }
    //----------------------------------------------------------
    public static function topLeftMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);

    }
    //----------------------------------------------------------
    public static function topRightUserMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------
    public static function sidebarMenu()
    {
        $links = [];


        $links[0] = [
            'icon' => 'table',
            'label'=> 'Appointment',
            'link'=> route('vh.backend.appointment'),
            'items' => [
                [
                    'link'=> self::$link,
                    'icon' => 'chart-bar',
                    'label'=> 'Dashboard'
                ],
                [
                    'link'=> self::$link."doctors",
                    'icon' => 'user',
                    'label'=> 'Doctors',
                ],
                [
                    'link'=> self::$link."patients",
                    'icon' => 'user-plus',
                    'label'=> 'Patients',
                ],
                [
                    'link'=> self::$link."appointments",
                    'icon' => 'calendar',
                    'label'=> 'My Appointments',
                ]
            ]
        ];


        if(version_compare(config('vaahcms.version'), '2.0.0', '<' )){
            $links[0]['link'] = route('vh.backend.appointment');
        } else{
            $links[0]['url'] = route('vh.backend.appointment');
        }


        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------

}
