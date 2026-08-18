<?php
namespace App\Http\Controllers;
use App\Services\FinanceNewsService;
class NewsController extends Controller{public function __invoke(FinanceNewsService $news){return view('news.index',['articles'=>$news->latest()]);}}
