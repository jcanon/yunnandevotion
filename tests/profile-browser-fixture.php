<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('local')) exit(1);
$email=getenv('YDA_QA_EMAIL');
if (!preg_match('/^qa-[a-f0-9]+@example\.test$/',$email)) exit(1);
if (($argv[1]??'')==='create') {
    $user=App\Models\User::create(['name'=>'QA researcher','email'=>$email,'password'=>getenv('YDA_QA_PASSWORD')]);
    $user->forceFill(['role'=>'admin','email_verified_at'=>now()])->save();
} else {
    $user=App\Models\User::where('email',$email)->first();
    if ($user) {
        $observations=Illuminate\Support\Facades\DB::table('observations')->where('author_id',$user->id)->get();
        $videos=Illuminate\Support\Facades\DB::table('interviews')->whereIn('observation_id',$observations->pluck('id'))->get();
        foreach($videos as $video) Illuminate\Support\Facades\Storage::disk('local')->delete(array_filter([$video->original_path,$video->web_path]));
        Illuminate\Support\Facades\DB::table('interviews')->whereIn('id',$videos->pluck('id'))->delete();
        $photos=Illuminate\Support\Facades\DB::table('photographs')->whereIn('observation_id',$observations->pluck('id'))->get();
        foreach($photos as $photo) Illuminate\Support\Facades\Storage::disk('local')->delete([$photo->original_path,$photo->web_path]);
        Illuminate\Support\Facades\DB::table('photographs')->whereIn('id',$photos->pluck('id'))->delete();
        Illuminate\Support\Facades\DB::table('observation_decisions')->whereIn('observation_id',$observations->pluck('id'))->delete();
        Illuminate\Support\Facades\DB::table('observations')->whereIn('id',$observations->pluck('id'))->delete();
        Illuminate\Support\Facades\DB::table('sites')->whereIn('id',$observations->pluck('site_id'))->whereNotIn('id',Illuminate\Support\Facades\DB::table('observations')->select('site_id'))->delete();
        Illuminate\Support\Facades\DB::table('sessions')->where('user_id',$user->id)->delete(); $user->delete();
    }
}
