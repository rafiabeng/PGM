<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PageTemplateController extends Controller
{
    private $page_title         = "Page Template";
    private $route              = "pages";
    private $permission         = "page-template";
    private $pageConfigs        = ['pageHeader' => false];

    function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:' . $this->permission . '.index|' . $this->permission . '.create|' . $this->permission . '.edit|' . $this->permission . '.delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:' . $this->permission . '.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->permission . '.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->permission . '.delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        // date_default_timezone_set('UTC');

        $hash = hash_hmac('sha256', '/v1.0/inquiry/010740025111569&verb=GET&token=Bearer 
        bee2Gj7bt70NVZ27DbdygDtLtVKD4d2V&timestamp=2021-08-01T09:05:25.311Z&body=', '6335d37c-4a33-4b90-afc0-87ce03fdb193');
        // dd(date('Y-m-d H:i:s')); 
        // dump($hash);
        // dump(base64_encode($hash));
        // dump(base64_decode(base64_encode($hash)));
        // dd(base64_decode('OzFKfUSTfwsyXXkoTfXuPsFj9lRD3eE8gwCbOkBFbI='));
        return view('master.page.index', [
            // 'breadcrumbs' => $breadcrumbs
            'pageConfigs'   => $this->pageConfigs,
            'page_title'    => $this->page_title,
            'permission'    => $this->permission,
            'route'         => $this->route,
        ]);
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
        $this->validate($request, [
            'code'          => 'required',
            'url'           => 'required',
            'description'   => 'required'
        ]);
        // dd($request->All());

        $page = Page::create($request->All());


        return redirect()->route($this->route . '.index')
        ->with(toaster('Page created successfully', 'success', 'success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page_show = Page::with('component')->find($id);
        // dd($page_show);
        return view('master.page.show', [
            'pageConfigs'   => $this->pageConfigs,
            'page_title'    => $this->page_title,
            'permission'    => $this->permission,
            'route'         => $this->route,
            'page_show'     => $page_show
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_edit    = Page::find($id);

        return view('master.page.index', [
            // 'breadcrumbs' => $breadcrumbs
            'pageConfigs'   => $this->pageConfigs,
            'page_title'    => $this->page_title,
            'route'         => $this->route,
            'page_edit'     => $page_edit
        ]);
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
        $this->validate($request, [
            'code'          => 'required',
            'url'           => 'required',
            'description'   => 'required'
        ]);

        $input = $request->all();

        $page = Page::find($id);
        $page->update($input);

        return redirect()->route($this->route . '.index')
        ->with(toaster('Page updated successfully', 'success', 'success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete_page = Page::find($id);
        $delete_page->delete() == true ?
            $return =
        ['code' => 'success', 'msg' => 'Page deleted successfully']
        : $return = ['code' => 'error', 'msg' => 'Something went wrong!'];

        return response()->json($return);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $this->type = $request['type'];
            $model = Page::query()->orderBy('created_at', 'DESC')->get();

            return DataTables::of($model)
            ->addIndexColumn()
            ->addColumn('created_at', function ($data) {
                return createdAt($data->created_at);
            })
            ->addColumn('action', function ($data) {
                $button = '<a href="' . route($this->route . '.show', $data->id) . '" class="btn btn-icon btn-warning btn-sm"  data-toggle="tooltip" title="Show">
                    ' . SVGI('bi-eye') . '
                    </a>';
                if (auth()->user()->can($this->permission . '.edit')) {
                    $button .= ' <a href="' . route($this->route . '.edit', $data->id) . '" class="btn btn-icon btn-primary btn-sm"  data-toggle="tooltip" title="Edit">
                    ' . SVGI('bi-pencil-square') . '
                    </a>';
                }
                if ($this->type == 'create') {
                    if (auth()->user()->can($this->permission . '.delete')) {
                        $button .= ' <button class="btn btn-icon btn-sm btn-delete btn-danger" data-remote="' . route($this->route . '.destroy', $data->id) . '" data-toggle="tooltip" title="Delete">
                            ' . SVGI('bi-trash') . '
                        </button>';
                    }
                }
                return $button;
            })
            ->rawColumns(['created_at', 'action'])
            ->make(true);
        }
    }
}
