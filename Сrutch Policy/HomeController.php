<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bbs;

class HomeController extends Controller
{
    private const BBS_VALIDATOR_RULS = ['title'=>'required|max:50',
                                      'content'=> 'required',
                                      'price'=>'required|numeric'];

    private const BBS_VALIDATOR_MESSAGES = ['price.required'=>'Раздавать товары бесплатно нельзя',
                                            'required' => 'Заполни это поле',
                                            'max'=>'значение не должно быть длиннее :max символов',
                                            'numeric'=>'Введи число'];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home', ['Bbs' => Auth::user()->bbs()->latest()->get()]);
    }

    //страница-форма добавления объявления
    public function addPage()
    {
        return view('addPage');
    }

    //добавление в базу данных объявления(из страница-форма добавления объявления)
    public function addToDB(Request $request)
    {
        //валидация данных в $request
        $validated = $request->validate(self::BBS_VALIDATOR_RULS, self::BBS_VALIDATOR_MESSAGES);
        Auth::user()->bbs()->create(['title' => $validated['title'],
                                    'content' => $validated['content'],
                                    'price' => $validated['price']]);
        return redirect()->route('home');
    }


//-----------------------------------------------------------------------------------------------------------
    //страница-форма редактирования объявления
    public function editPage($idItem) //$idItem сдесь можно назвать как угодно
    {
        $context = ['Bbs' => Bbs::find($idItem)]; //есть сокращенная запись стр.70

        /* Проверка, принадлежит ли пользователю объявление.
            !!! Внимание, в Laravel такая проверка должна происходить через "Политики".
            Route->Middleware->BbsPolicy такая политика предусмотренна, но по неизвестной причине не работает.
            Это пример "Костыля".
            Взят с https://www.youtube.com/watch?v=MYyJ4PuL4pY (в конце видео User Autorisation).
        */
        if ($context['Bbs']['user_id'] != auth()->id()) {
            abort(403, 'Unauthorized Action!');
        }

        return view('editPage', $context);
    }
//----------------------------------------------------------------------------------------------------------



    //обновить в базе данных объявление(из страница-форма редактиролвания объявления)
    public function updateTupleDB(Request $request, $idItem)
    {
        //валидация данных в $request
        $validated = $request->validate(self::BBS_VALIDATOR_RULS, self::BBS_VALIDATOR_MESSAGES);
        //запрос в БД через модель Bbs
        $objBbs = Bbs::find($idItem);
        //fill - массовое присвоение стр.70
        $objBbs->fill(['title' => $validated['title'],
                        'content' => $validated['content'],
                        'price' => $validated['price']]);
        $objBbs->save();
        return redirect()->route('home');
    }

    //страница-форма удаления объявления
    public function deletePage($idItem)
    {
        $context = ['Bbs' => Bbs::find($idItem)];
        return view('deletePage', $context);
    }

    //удалить в базе данных объявление(из страницы-формы удаления объявления)
    public function deleteTupleDB($idItem)
    {
        $objBbs = Bbs::find($idItem);
        $objBbs->delete();
        return redirect()->route('home');
    }
}
