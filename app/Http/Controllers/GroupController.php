<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Repositories\ParseArchiveRepository;
use App\Repositories\ParseCategoryRepository;
use App\Repositories\ParseGroupRepository;
use App\Repositories\ParseItemRepository;
use App\Repositories\ParseMenuRepository;
use App\Repositories\ParseSubCategoryRepository;
use App\Repositories\ParseUserRepository;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    private $items;

    private $archives;

    private $menu;

    private $categories;

    private $subcategories;

    private $groups;

    private $users;

    public function __construct(ParseGroupRepository $groups, ParseUserRepository $users, ParseItemRepository $items, ParseArchiveRepository $archives, ParseMenuRepository $menu, ParseCategoryRepository $categories, ParseSubCategoryRepository $subcategories)
    {
        $this->subcategories = $subcategories;
        $this->items = $items;
        $this->archives = $archives;
        $this->menu = $menu;
        $this->categories = $categories;
        $this->groups = $groups;
        $this->users = $users;
        // $this->middleware('auth', ['except' => 'index']);
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account = null)
    {
        $group = $this->groups->findBy('account', $account);
        // $allMenus = $this->menu->all();
        // foreach ($allMenus as $key => $menu) {
        //     $this->menu->update($menu->objectId, ['group' => $group]);
        // }
        // dd($allMenus);
        return empty($group) ? view('/auth/register') : view('/auth/login')->with('group', $group);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    public function isUniqueUser($email)
    {
        return empty($this->users->findBy('email', $email));
    }

    public function handleStep($request, $step)
    {
        switch (intval($step)) {
            case 0:
                return $this->isUniqueUser($request->email);
                break;

            default:
                # code...
                break;
        }
    }

    public function step(Request $request, $step)
    {
        // dd($this->handleStep($request, $step));
        return $this->handleStep($request, $step) ? response()->json(true, 200) : response()->json(false, 422);
        //dd($request->all(),$step);
    }
}
