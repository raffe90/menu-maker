<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\ParseClasses\Archive;
use App\ParseClasses\Item;
use App\ParseClasses\Menu;
use App\Repositories\ParseArchiveRepository;
use App\Repositories\ParseCategoryRepository;
use App\Repositories\ParseGroupRepository;
use App\Repositories\ParseItemRepository;
use App\Repositories\ParseMenuRepository;
use App\Repositories\ParseSubCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Parse\ParseQuery;
use Parse\ParseRole;
use Parse\ParseUser;
use \Carbon\Carbon as Carbon;

class MenuController extends Controller
{
	private $items;

  private $archives;

  private $menu;

  private $categories;

  private $groups;

	public function __construct(ParseGroupRepository $groups, ParseItemRepository $items, ParseArchiveRepository $archives, ParseMenuRepository $menu, ParseCategoryRepository $categories)
	{
    $this->items = $items;
    $this->archives = $archives;
    $this->menu = $menu;
    $this->categories = $categories;
    $this->middleware('auth');
    $this->groups = $groups;
    parent::__construct();
	}

	public function index()
	{

    // $query = new ParseQuery('_User');
    // // $user = $query->get('3rU7jrnGJ8');
    // //
    // $user = \Auth::user();
    // // dd($user->groups->getQuery()->find());
    // $groups = $user->getRelation('groups');

    // $groupQuery = new ParseQuery("Group");
    // $group = $groupQuery->equalTo("account",'demo')->first();

    // $groups->add($group);
    // // dd($group);
    // // $user->set('groups', $group);
    // $user->save(true);

    // dd($user->groups->getQuery()->find());

		return view('menu.index');
	}

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($account, $name, $version = null)
  {
    if(strcmp($name, 'wine-list') == 0){
      return (strcmp($version, 'shortened') == 0 ) ? view('wine.show_shortened') : view('wine.show');
    }
    else{
      return view('menu.show');
    }
  }

  public function edit($account, $name, $version = null)
  {
    if(strcmp($name, 'wine-list') == 0){
      return (strcmp($version, 'shortened') == 0 ) ? view('wine.edit_shortened') : view('wine.edit');
    }
    else{
      return view('menu.edit');
    }
    // return strcmp($name, 'wine-list') == 0 ? view('wine.edit') : view('menu.edit');
  }

  public function store($content, $menu)
  {
    return $this->archives->create(['name'=> Carbon::now()->format('Y-m-d'), 'content' => $content, 'menu' => $menu]);
  }

  public function storeOrUpdate($account, $name, $version = null)
  {


    // $menu = $this->menu->findBy('name', str_replace('-', ' ', $name));

    $group = $this->groups->findBy('account', $account);
    $menus = $this->menu->findAllBy('group', $group);
    $menu = $menus->filter(function($menuItem) use ($name){
      return strcmp($menuItem->name, str_replace('-', ' ', $name))==0;
    })->first();
    // $menu = $this->menu->findBy('name', str_replace('-', ' ', $name));

    if(strcmp($name, 'wine-list') == 0){
      $menuData = $this->makeWineMenu($menu);
      if(strcmp($version, 'shortened') == 0 ){
        $_menuPartial = view()->make('partials._wine_shortened', $menuData)->render();
      }
      else{
        $_menuPartial = view()->make('partials._wine', $menuData)->render();
      }
    }
    else{
      $menuData = $this->makeMenu($menu);
      $menuData["archive"] = true;
      $_menuPartial = view()->make('partials._columns', $menuData)->render();
      // $_menuPartial = view()->make('partials.archives._menu', $menuData)->render();
    }
    $archives = $this->archives->findAllBy('menu', $menu);
    if($archives->contains('name', Carbon::now()->format('Y-m-d'))){
      $this->update($_menuPartial);
    }
    else{
      $this->store($_menuPartial, $menuData['menu']);
    }
    // $this->handlePDFBackup($menu);
    flash()->overlay('Your menu configuration has been saved correctly', 'This menu will be displayed on the Archive section');
    return redirect('/admin/menus/'.$name);
  }

  public function handlePDFBackup($menu)
  {
    $file = 'archive/menu'.Carbon::now()->format('Y-m-d').'.pdf';
    $pdf = \PDF::loadView('pdf.show', $menu);
    if (\File::exists($file)) \File::delete($file);
    return $pdf->save($file);
  }

  public function makeMenu($menu)
  {
      $categories = $this->categories->findAllBy('menu', $menu, [], 1000, true, 'position');
      $items = $this->items->findAllBy('menu', $menu, ['category'], 1000, true, 'position');
      return ['categories' => $categories, 'items' => $items, 'menu' => $menu];
  }

  public function makeWineMenu($menu)
  {
      $subcategoryRepo = new ParseSubCategoryRepository();
      $subcategories = $subcategoryRepo->findAllBy('menu', $menu, ['category'], 1000, true, 'position');
      $categories = $this->categories->findAllBy('menu', $menu, [], 1000, true, 'position');
      $items = $this->items->findAllBy('menu', $menu, ['category'], 1000, true, 'position');
      return ['subcategories' => $subcategories, 'categories' => $categories, 'items' => $items, 'menu' => $menu];
  }

  public function update($_menu)
  {
    $menu = $this->archives->findBy('name', Carbon::now()->format('Y-m-d'));
    return $this->archives->update($menu->objectId, ['content' => $_menu]);
  }

  public function archive($account, $name)
  {
    // $menu = $this->menu->findBy('name', str_replace('-', ' ', $name));
    $group = $this->groups->findBy('account', $account);
    $menus = $this->menu->findAllBy('group', $group);

    // dd($group, $menus);
    $menu = $menus->filter(function($menuItem) use ($name){
      return strcmp($menuItem->name, str_replace('-', ' ', $name))==0;
    })->first();
    $archives = $this->archives->findAllBy('menu', $menu, ['menu']);
    return view('archives.index', compact('archives', 'menu'));
  }
}
