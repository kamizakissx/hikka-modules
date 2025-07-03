<?php
ob_start();
error_reporting(0);
date_default_timezone_set("Asia/Tashkent");

if(!file_exists('madeline.php')){
copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}

if(!file_exists('config.json')){
file_put_contents('config.json', '{"clock":0,"read":0,"video":0,"sticker":0,"game":0,"voice":0,"typing":0,"smile":1,"online":0}');
}

include 'madeline.php';

///Biz Sizning Harakatingizga javob bermaymiz 
///va qilishga undamaymiz.
///Ushbu Kod @uzb_cristal Tegishli 
/// Old Tarqalgan versiyasi ham bor
///Bu yangilangan versiya 
///Manba : @UzCoder_Kanal


use danog\MadelineProto\EventHandler;
use \danog\Loop\Generic\GenericLoop;
use \danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;

class MyEventHandler extends EventHandler
{
const Admins = [id]; //User bot oʻrnatilgan Telegram ID raqam yoziladi!
const Report = 'user'; //User bot dagi error yoki turli xil "log" lar yuborib turilishi uchun kanal, guruh yoki xohlagan username yoziladi!

public function getReportPeers(){
return [self::Report];
}

public function genLoop(){
$this->account->updateStatus([
'offline' => false
]);
return 60000;
}

public function onStart(){
$genLoop = new GenericLoop([$this, 'genLoop'], 'update Status');
$genLoop->start();
}

public function onUpdateNewChannelMessage($update){
$this->onUpdateNewMessage($update);
}

public function onUpdateNewMessage($update){
if(time() - $update['message']['date'] > 2){
return;
}
try{
$data          = json_decode(file_get_contents("config.json"), true);
$replyToId     = $update['message']['reply_to']['reply_to_msg_id']?? 0;
$text = $update['message']['message']?? null;
$mid = $update['message']['id']?? 0;
$fid = $update['message']['from_id']['user_id']?? 0;
$rmid = $update['message']['reply_to']['reply_to_msg_id']?? 0;
$peer = $this->getID($update);

if((int)json_decode(file_get_contents('config.json'))->read == 1){
if($peer < 0){
$this->channels->readHistory([
'channel' => $peer,
'max_id' => $mid
]);
$this->channels->readMessageContents([
'channel' => $peer,
'id' => [$mid]
]);
}else{
$this->messages->readHistory([
'peer' => $peer,
'max_id' => $mid
]);
}
}

if((int)json_decode(file_get_contents('config.json'))->typing == 1){
$sendMessageTypingAction = ['_' => 'sendMessageTypingAction'];
$this->messages->setTyping([
'peer' => $peer,
'action' =>$sendMessageTypingAction
]);
}

if((int)json_decode(file_get_contents('config.json'))->video == 1){
$sendMessageRecordVideoAction = ['_' => 'sendMessageRecordVideoAction'];
$this->messages->setTyping(['peer' => $peer, 'action' => $sendMessageRecordVideoAction]);
}

if((int)json_decode(file_get_contents('config.json'))->sticker == 1){
$sendMessageTypingAction = ['_' => 'sendMessageChooseStickerAction'];
$this->messages->setTyping(['peer' => $peer, 'action' => $sendMessageTypingAction]);
}

if((int)json_decode(file_get_contents('config.json'))->game == 1){
$sendMessageGamePlayAction = ['_' => 'sendMessageGamePlayAction'];
$this->messages->setTyping(['peer' => $peer, 'action' => $sendMessageGamePlayAction]);
}
if((int)json_decode(file_get_contents('config.json'))->voice == 1){
$sendMessageRecordAudioAction = ['_' => 'sendMessageRecordAudioAction'];
$this->messages->setTyping(['peer' => $peer, 'action' => $sendMessageRecordAudioAction]);
}

if((in_array($fid, self::Admins))){
if($text == ".help"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>😎 User bot buyruqlari:

.help - 🖥 User botdan foydalanish boʻyicha qoʻllanma!

.ping - 🚀 User bot tezligini tekshirish!

.restart - 🔄 User botni qayta ishga tushirish va yangilash!

.status - 💾 User bot serverda qancha joy band qilayotganini tekshirish!

.function - 🌐 Userbotning Barcha Funksiyalar!

.mode - 🌐 UserBotning on|off funksiyalari!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".mode"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "<b>
.read on - 📑 Avtomatik oʻqish rejimini yoqish!

.read off - 📑 Avtomatik oʻqish rejimini oʻchirish!

.video on - 🎬 Video yubormoqda... rejimini yoqish!

.video off - 🎬 Video yubormoqda... rejimini oʻchirish!

.audio on - 📽 Audio yubormoqda... rejimini yoqish!

.audio off - 📽 Audio yubormoqda... rejimini o'chirish!

.sticker on - 🌠 Sticker Yubormoqda... rejimini yoqish!

.sticker off - 🌠 Sticker Yubormoqda... rejimini O‘chirish!

.game on - 🎮 O'yin O'ynamoqda... rejimini yoqish!

.game off - 🎮 O'yin O'ynamoqda... rejimini O‘chirish!

.typing on - 📝 Yozmoqda... rejimini yoqish!

.typing off - 📝 Yozmoqda... rejimini oʻchirish!

.online on - 🖥 24 soat online rejimini yoqish!

.online off - 🖥 24 soat online rejimini oʻchirish!  </b>",
'parse_mode' => 'html',
]);
}

$info = $this->getInfo($update);
$type = $info['type'];
if(preg_match("/^[\/\#\!]?(voice) (.*)$/i", $text, $m)){
if($type == "supergroup"||$type == "chat"||$type == 'user'){
$mu = $m[2];
$this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "( `$m[2]` ) nomli ovoz qidirilmoqda . . . !", 'parse_mode' => 'markdown' ]);
$messages_BotResults = $this->messages->getInlineBotResults(['bot' => "@ovozqanibot", 'peer' => $peer, 'query' => $mu, 'offset' => '0']);
$query_id = $messages_BotResults['query_id'];
$query_res_id = $messages_BotResults['results'][rand(0, count($messages_BotResults['results']))]['id'];
$this->messages->sendInlineBotResult(['silent' => true, 'background' => false, 'clear_draft' => true, 'peer' => $peer, 'reply_to_msg_id' => $mid, 'query_id' => $query_id, 'id' => "$query_res_id"]);
}}

if(preg_match("/^[\/\#\!]?(spam) ([0-9]+) (.*)$/i", $text, $m)){
$count = $m[2];
$txt = $m[3];
for($i=1; $i <= $count; $i++){
 $this->messages->sendMessage(['peer' => $peer, 'message' => $txt]);
}
}

if($text == ".func"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>.love - ❤️ Animatsiyali yurakchalar funksiyasi!

.police - 🟥🟦🟥 Animatsiyali Police funksiyasi!

.ari - 🐝 Animatsiyali Ari funksiyasi!

.fuck - 🖕 Animatsiyali Fuck funksiyasi! 

.yurak - ❤️ Animatsiyali yurakchalar 2 funksiyasi!

.knife - 🔪 Animatsiyali Knife funksiyasi!

.chaqmoq - ⚡️Animatsiyali Chaqmoq funksiyasi!

.kill - 🔫 Animatsiyali Kill funksiyasi!

.load - ▪️ Animatsiyali Error funksiyasi!

.god - 🕌 Animatsiyali 🕌 funksiyasi!
 
.dush - 🛁 Animatsiyali Dush funksiyasi!

.snake - 🐍 Animatsiyali Snake funksiyasi!

.ghost - 👻 Animatsiyali Ghost funksiyasi!

.cosmo - 🚀 Animatsiyali Cosmo funksiyasi!

.dance - 💃 Animatsiyali Dance funksiyasi!

.ayriliq - 💔 Animatsiyali Ayriliq funksiyasi!

.home - 🏠 Animatsiyali Home funksiyasi!

.puq - 💩 Animatsiyali Puq funksiyasi!

.money - 💸 Animatsiyali Money funksiyasi!

.search - 🔦 Animatsiyali Search funksiyasi!

.lovee - 🖤 Animatsiyali Love funksiyasi!</b>',
'parse_mode' => 'html'
]);
}

if($text == ".func2"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>.fuck2 - 🖕 Animatsiyali Fuck funksiyasi 2 !

.fuck3 - 🖕 Animatsiyali Fuck funksiyasi 3 !

.xd - 🤣 Animatsiyali Kulgu funksiyasi!

.snow - ❄️ Animatsiyali Qor funksiyasi!

.kub - 🔵🔴 Animatsiyali Kubik funksiyasi!

.voice - 🌐  Audio izlash!</b>',
'parse_mode' => 'html',
]);
}



if($text == ".function"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>.func - ✅️ Funksiyalar jamlanmasi 

.func2 - ✅️ Funksiyalar jamlanmasi 2</b>',
'parse_mode' => 'html',
]);
}
if ($text == '.kub') {
 $this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);
$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);
$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
??🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);
$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);$this->messages->editMessage([
'peer' => $peer,
'id' =>$mid,
'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥
🟥🔲🔳🔲🟥
🟥🟥🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid,'message' => '🟥🟥🟥🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🔲🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🔳🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟥🟥🟥🟥🟥
🟥🔲🟥🟥🟥
🟥🟥🔳🟥🟥
🟥🟥🟥🔲🟥
🟥🟥🟥🟥🟥']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🟪🟪
🟪🔲🔳🔲🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🔲🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🔳🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟪🟪🟪🟪🟪
🟪🔲🟪🟪🟪
🟪🟪🔳🟪🟪
🟪🟪🟪🔲🟪
🟪🟪🟪🟪🟪']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦
🟦🔲🔳🔲🟦
🟦🟦🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🔲🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🔳🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟦🟦🟦🟦🟦
🟦🔲🟦🟦🟦
🟦🟦🔳🟦🟦
🟦🟦🟦🔲🟦
🟦🟦🟦🟦🟦']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '◻️🟩🟩◻️◻️
◻️◻️🟩◻️🟩
🟩🟩🔳🟩🟩
🟩◻️🟩◻️◻️
◻️◻️🟩🟩◻️']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '🟩⬜️⬜️🟩🟩
🟩🟩⬜️🟩⬜️
⬜️⬜️🔲⬜️⬜️
⬜️🟩⬜️🟩🟩
🟩🟩⬜️⬜️🟩']);
 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => '▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️
▫️▫️▫️▫️▫️']);

 $this->messages->editMessage(['peer' => $peer, 'id' =>$mid, 'message' => 'Kubik rubik terildi✅️🔵🔴🔵']);
}

elseif ($text == '.fuck3' or $text == 'fuck3') {
           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
              $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
              $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
              $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
              $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
        } 

elseif($text == ".snow"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '☀️',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
   ☀️
☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => " 
     ☀️
☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️
           💧💧💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️☁️☁️☁️
          ☁️☁️☁️☁️☁️☁️  
 💧  💧💧  💧💧  💧💧    💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
             ❄️
     💧    💧💧💧💧

💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "          ☀️
☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
             ❄️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
         ❄️          ❄️
 ❄️    ❄️   ❄️       ❄️  
❄️   ❄️      ❄️ ❄️",
]);  
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️

            ❄️
                    ❄️
                    ❄️   
              ❄️   ❄️
                 ❄️  
          ❄️  
       ❄️           ❄️
            ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️

       ❄️          ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
☁️  ☁️☁️
☁️            ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌥
☁️  ☁️ ☁️
    ❄️

      ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
     ☀️
☁️☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
                        ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️                       🌙",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌛",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
   ☀️
☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => " 
     ☀️
☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️
           💧💧💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️☁️☁️☁️
          ☁️☁️☁️☁️☁️☁️  
 💧  💧💧  💧💧  💧💧    💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
             ❄️
     💧    💧💧💧💧

💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "          ☀️
☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
             ❄️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
         ❄️          ❄️
 ❄️    ❄️   ❄️       ❄️  
❄️   ❄️      ❄️ ❄️",
]);  
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️

            ❄️
                    ❄️
                    ❄️   
              ❄️   ❄️
                 ❄️  
          ❄️  
       ❄️           ❄️
            ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️                 ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
☁️  ☁️☁️
☁️            ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌥
☁️  ☁️ ☁️
    ❄️

      ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
     ☀️
☁️☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
                        ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️                       🌙",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌛",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
   ☀️
☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => " 
     ☀️
☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️ ☁️ ☁️  ☁️ ☁️ ☁️
           💧💧💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️☁️☁️☁️
          ☁️☁️☁️☁️☁️☁️  
 💧  💧💧  💧💧  💧💧    💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️                       ☁️
             ❄️
     💧    💧💧💧💧

💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "          ☀️
☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
             ❄️
💧 💧 💧 💧 💧 💧",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ❄️          ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
         ❄️          ❄️
 ❄️    ❄️   ❄️       ❄️  
❄️   ❄️      ❄️ ❄️",
]);  
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️

            ❄️
                    ❄️
                    ❄️   
              ❄️   ❄️
                 ❄️  
          ❄️  
       ❄️           ❄️
            ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️





       ❄️          ❄️
 ❄️   ❄️   ❄️    ❄️  
❄️  ❄️     ❄️❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☁️☁️☁️☁️☁️☁️☁️
    ☁️☁️☁️☁️☁️
☁️            ☁️
       ",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
☁️  ☁️☁️
☁️            ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌥
☁️  ☁️ ☁️
    ❄️

      ❄️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "
     ☀️
☁️☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️
                        ☁️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "☀️                       🌙",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌛",
]);
}

///function

elseif($text == ".xd"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🤣',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤣🤣",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "😂",
]);
}


if(preg_match("/^[\/\#\!]?(flood) ([0-9]+) (.*)$/i", $text, $m)){
$count = $m[2];
$txt = $m[3];
$spm = "";
for($i=1; $i <= $count; $i++){
$spm .= " $txt \n";
}
 $this->messages->sendMessage(['peer' => $peer, 'message' => $spm]);
     }

if ($text == '.fuck2' or $text == '.Fuck2') {
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🏿🖕🖕🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🏿🖕🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🏿🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🏿🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🖕🏿🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🖕🖕🏿']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🖕🏾🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🏿🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🏿🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🏿🖕🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🏿🖕🖕🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🏿🖕🖕🏿🖕🖕🏿']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🏿🖕🖕🏿🖕🖕🏿🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🖕🖕🖕🖕🖕']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖕🏿🖕🏿🖕🏿🖕🏿🖕🏿🖕🏿']);
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🖤fucking you🖤']);
}

elseif($text=='.lovee'){
 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀________________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀_______________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀______________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀_____________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀____________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀___________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀__________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀_________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀________🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀_______🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀______🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀____🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀___🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '🚶‍♀__🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' =>'🚶‍♀_🏃‍♂']);

 $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '💙love💙']);
}

elseif ($text == '.search') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                     🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                    🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                   🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                  🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                 🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽                🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽               🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽              🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽             🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽            🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽           🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽          🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽         🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽        🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽       🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽      🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽     🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽    🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽   🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽  🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽 🔦😼"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👽🔦🙀"]);
}
elseif ($text == '.cosmo') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                                🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                               🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                              🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                             🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                            🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                           🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                          🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                         🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                        🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                       🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                      🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                     🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                   🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                  🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                 🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀                🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀               🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀              🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀            🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀           🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀          🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀         🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀        🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀       🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀      🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀     🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀    🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀   🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀  🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀 🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍🚀🛸"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🌍💥Boom💥"]);
}
elseif ($text == '.money') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌                    💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌                   💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌                 💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌                💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌               💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌              💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌             💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌            💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌           💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌          💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥                     💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌        💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌       💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌      💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌     💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌    💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌   💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌  💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌ 💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥            ‌💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥           💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥          💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥         💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥        💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥       💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥      💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥     💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥    💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥   💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥  💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔥 💵"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💸"]);
}
elseif ($text == '.puq'){
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩               🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩              🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩             🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩            🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩           🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩          🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩         🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩        🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩       🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩      🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩     🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩    🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩   🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩  🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💩 🤢"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🤮🤮"]);
}
elseif ($text == '.ghost'){
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                                   🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                                  🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                                 🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                                🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                               🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                              🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                             🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                            🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                           🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                          🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                         🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                        🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                       🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                      🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                     🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                    🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                   🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                  🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻                 🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻               🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻              🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻             🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻            🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻           🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻          🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻         🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻        🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻       🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻      🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻     🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻    🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻   🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻  🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻 🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "👻🙀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☠Kill☠"]);
}
elseif ($text == '.home') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠              🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠             🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠            🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠           🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠          🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠         🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠        🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠       🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠      🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠     🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠    🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠   🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠  🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠 🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏠🚶‍♂"]);
}
elseif ($text == '.ayriliq') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "❤️🧡💛💚"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💜💙🖤💛"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🤍🤎💛💜"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💚❤️🖤🧡"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💜💚🧡🖤"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🤍🧡🤎💜"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💙🧡💜🧡"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💚💛💙💜"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🖤💛💙🤍"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🖤🤍💙❤"]);
}
elseif ($text == '.dance') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡 💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡  💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡   💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡    💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡     💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡      💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡       💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡        💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡         💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡          💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡           💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡            💃"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡              💃💔👫"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡                 🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡               🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡             🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡           🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡         🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡       🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡     🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡  🚶‍♀"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏡🚶‍♀"]);
}
elseif ($text == '.snake') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍                         🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍                      🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍                    🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍                  🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍                🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍               🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍              🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍            🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍           🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍          🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍         🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍        🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍       🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍      🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍     🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍    🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍   🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍 🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🐍🦅"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😹"]);
}
elseif ($text == '.dush'){
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪                  🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪                 🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪                🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪              🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪             🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪            🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪           🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪          🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪         🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪        🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪       🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪      🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪     🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪    🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪   🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪  🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪 🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛁🚪🗝🤏"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🛀💦😈"]);
}
elseif ($text == '.load'){
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️10%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️20%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️30%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️40%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️▪️50%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️▪️▪️60%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️▪️▪️▪️70%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️▪️▪️▪️▪️80%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "▪️▪️▪️▪️▪️▪️▪️▪️▪️90%"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "❗️ERROR❗️"]);
}
elseif ($text == '.kill') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂                 • 🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂                •  🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂               •   🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂              •    🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂             •     🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂            •      🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂           •       🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂          •        🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂         •         🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂        •          🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂      •           🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂      •            🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂     •             🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂    •              🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂   •               🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂  •                🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂 •                 🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "😂•                  🔫🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🤯                  🔫 🤠"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🤠"]);
}
elseif ($text == '.god') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌                  🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌                 🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌                🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌               🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌              🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌             🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌            🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌           🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌          🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌         🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌        🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌       🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌      🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌     🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌    🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌   "]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌  🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌 🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🕌🚶‍♂"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "اشه Ey Ollohni Unutmaylik !"]);
}
elseif ($text == '.dengiz') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅┄┅┄┄┅🏊‍♂┅┄┄┅🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅┄┅┄┄🏊‍♂┅┄┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅┄┅┄🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅┄┅🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅┄🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄┅🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝┄🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🏝🏊‍♂┅┄🦈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🦈"]);
}
elseif ($text == '.chaqmoq') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️                ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️               ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️              ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️             ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️            ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️           ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️          ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️         ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️        ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️       ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️      ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️     ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️    ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️   ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️  ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "☁️ ⚡️"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "⛈"]);
}
elseif ($text == '.knife') {
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪                🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪               🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪              🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪             🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪            🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪           🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪          🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪         🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪        🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪       🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪      🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪     🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪    🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪   🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪  🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪 🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "🔪🎈"]);
 $this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => "💥Boom💥"]);
}

///tugadi
elseif ($text == '.fuck') {
           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => "                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);

           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => " .                        /¯)
                        /   /
                     /    /
             /´¯/'   '/´¯¯•¸
          /'/   /    /  /     /¨¯\
        ('(   (   (   (  ¯~/'  ' /
         \                         /
          \                _.•´
            \              (
              \             \ ' "]);
        } 

elseif ($text == '.yurak') {


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           ❤️                  ❤️
        ❤️  ❤️          ❤️  ❤️
    ❤️          ❤️  ❤️          ❤️
       ❤️           ❤️           ❤️
           ❤️                    ❤️
               ❤️            ❤️
                   ❤️    ❤️
                        ❤️
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           🧡                  🧡
        🧡  🧡          🧡  🧡
    🧡          🧡  🧡          🧡
       🧡           🧡           🧡
           🧡                    🧡
               🧡            🧡
                   🧡    🧡
                        🧡
.']);


           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💛                  💛
        💛  💛          💛  💛
    💛          💛  💛          💛
       💛           💛           💛
           💛                    💛
               💛            💛
                   💛    💛
                        💛
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💚                  💚
        💚  💚          💚  💚
    💚          💚  💚          💚
       💚           💚           💚
           💚                    💚
               💚            💚
                   💚    💚
                        💚
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💙                  💙
        💙  💙          💙  💙
    💙          💙  💙          💙
       💙           💙           💙
           💙                    💙
               💙            💙
                   💙    💙
                        💙
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💜                  💜
        💜  💜          💜   💜
    💜          💜  💜          💜
       💜           💜           💜
           💜                    💜
               💜            💜
                   ??    💜
                        💜
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           🖤                  🖤
        🖤  🖤          🖤   🖤
    🖤          🖤  🖤          🖤
       🖤           🖤           🖤
           🖤                    🖤
               🖤            🖤
                   🖤    🖤
                        🖤
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           🤍                  🤍
        🤍  🤍          🤍   🤍
    🤍          🤍  🤍          🤍
       🤍           🤍           🤍
           🤍                    🤍
               🤍            🤍
                   🤍    🤍
                        🤍
.']);


           $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💗                  💗
        💗  💗          💗   💗
    💗          💗  💗          💗
       💗           💗           💗
           💗                    💗
               💗            💗
                   💗    💗
                        💗
.']);

            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           ❤️                  ❤️
        ❤️  ❤️          ❤️  ❤️
    ❤️          ❤️  ❤️          ❤️
       ❤️           ❤️           ❤️
           ❤️                    ❤️
               ❤️            ❤️
                   ❤️    ❤️
                        ❤️
.']);

            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           🧡                  🧡
        🧡  🧡          🧡  🧡
    🧡          🧡  🧡          🧡
       🧡           🧡           🧡
           🧡                    🧡
               🧡            🧡
                   🧡    🧡
                        🧡
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💛                  💛
        💛  💛          💛  💛
    💛          💛  💛          💛
       💛           💛           💛
           💛                    💛
               💛            💛
                   💛    💛
                        💛
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💚                  💚
        💚  💚          💚  💚
    💚          💚  💚          💚
       💚           💚           💚
           💚                    💚
               💚            💚
                   💚    💚
                        💚
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💙                  💙
        💙  💙          💙  💙
    💙          💙  💙          💙
       💙           💙           💙
           💙                    💙
               💙            💙
                   💙    💙
                        💙
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💜                  💜
        💜  💜          💜   💜
    💜          💜  💜          💜
       💜           💜           💜
           💜                    💜
               💜            💜
                   💜    💜
                        💜
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           ❤️                  ❤️
        ❤️  ❤️          ❤️  ❤️
    ❤️          ❤️  ❤️          ❤️
       ❤️           ❤️           ❤️
           ❤️                    ❤️
               ❤️            ❤️
                   ❤️    ❤️
                        ❤️
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           🧡                  🧡
        🧡  🧡          🧡  🧡
    🧡          🧡  🧡          🧡
       🧡           🧡           🧡
           🧡                    🧡
               🧡            🧡
                   🧡    🧡
                        🧡
.']);


            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '.           💛                  💛
        💛  💛          💛  💛
    💛          💛  💛          💛
       💛           💛           💛
           💛                    💛
               💛            💛
                   💛    💛
                        💛

.']);

            $this->messages->editMessage(['peer' => $peer, 'id' => $mid, 'message' => '💜']);
        }

elseif($text == ".ping"){
$start_time = round(microtime(true) * 1000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🚀 Ping: Tekshirilmoqda...</b>',
'parse_mode' => 'html'
]);
$end_time = round(microtime(true) * 1000);
$time_taken = $end_time - $start_time;
$this->messages->sendMessage([
'peer' => $peer,
'message' => '<b>🚀 Ping: ' . $time_taken . ' ms</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".restart"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🔄 User bot qayta yuklandi!</b>',
'parse_mode' => 'html'
]);
$this->restart();
}

elseif($text == ".ari") {
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🏥__________🏃‍♂️______________🐝',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🏥______🏃‍♂️_______🐝',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🏥______🏃‍♂️_____🐝',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🏥___🏃‍♂️___🐝',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid, 
'message' => '🏥_🏃‍♂️_🐝',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => 'Tugadi..☹️🐝',
]);
}

elseif($text == ".status"){
$answer = '<b>💾 Xotiradan foydalanish: ' . round(memory_get_peak_usage(true) / 1021 / 1024, 2) . ' Mb</b>';
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => $answer,
'parse_mode' => 'html'
]);
}



elseif($text == ".video on"){
$config = json_decode(file_get_contents('config.json'));
$config->video = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥  Video yubormoqda rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".video off"){
$config = json_decode(file_get_contents('config.json'));
$config->video = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥 Video yubormoqda rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".sticker on"){
$config = json_decode(file_get_contents('config.json'));
$config->sticker = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥  Sticker yubormoqda... rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".sticker off"){
$config = json_decode(file_get_contents('config.json'));
$config->sticker = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥 Sticker yubormoqda... rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".game on"){
$config = json_decode(file_get_contents('config.json'));
$config->game = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥  Õyin õynamoqda... rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".game off"){
$config = json_decode(file_get_contents('config.json'));
$config->game = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥 õyin õynamoqda... rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".audio on"){
$config = json_decode(file_get_contents('config.json'));
$config->voice = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥  Audio yubormoqda... rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".audio off"){
$config = json_decode(file_get_contents('config.json'));
$config->voice = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥  Audio yubormoqda... rejimi o‘chirildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".read on"){
$config = json_decode(file_get_contents('config.json'));
$config->read = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>📑 Avtomatik oʻqish rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}


elseif($text == ".read off"){
$config = json_decode(file_get_contents('config.json'));
$config->read = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>📑 Avtomatik oʻqish rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".typing on"){
$config = json_decode(file_get_contents('config.json'));
$config->typing = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>📝 Yozmoqda... rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".typing off"){
$config = json_decode(file_get_contents('config.json'));
$config->typing = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>📝 Yozmoqda... rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}

if(preg_match("/^[\/\#\!]?(music) (.*)$/i", $text, $m)){
if($type == "supergroup"||$type == "chat"||$type == 'user'){
$mu = $m[2];
$this->messages->editMessage(['peer' => $peer,'id' => $mid,'message' => " ( `$m[2]` ) Nomli musiqa qidirilmoda . . . !", 'parse_mode' => 'markdown' ]);
$messages_BotResults = $this->messages->getInlineBotResults(['bot' => "@anymelody_bot", 'peer' => $peer, 'query' => $mu, 'offset' => '0']);
$query_id = $messages_BotResults['query_id'];
$query_res_id = $messages_BotResults['results'][rand(0, count($messages_BotResults['results']))]['id'];
$this->messages->sendInlineBotResult(['silent' => true, 'background' => false, 'clear_draft' => true, 'peer' => $peer, 'reply_to_msg_id' => $mid, 'query_id' => $query_id, 'id' => "$query_res_id"]);
}}


elseif($text == ".weather"){
$ricon = array('01d'=>'🌞','02d'=>'🌤','03d'=>'☁️','04d'=>'🌥','09d'=>'🌦','10d'=>'🌧','11d'=>'⛈','13d'=>'❄️','50d'=>'💨','01n'=>'🌙','02n'=>'☁️','03n'=>'☁️','04n'=>'🌩','09n'=>'🌧','10n'=>'🌧','11n'=>'⛈','13n'=>'❄️','50n'=>'💨');
$obuhavo = json_decode(file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Qarshi,UZ&units=metric&appid=a9d86a9dc54f8caf39ac424735ffc2e6"),true);
$temp = $obuhavo['main']['temp'];
$icon = $obuhavo['weather'][0]['icon'];
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🌏Hozida `$temp` C°",
'parse_mode' => 'markdown'
]);
}

elseif($text == ".online on"){
$config = json_decode(file_get_contents('config.json'));
$config->online = 1;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥 24 soat online rejimi yoqildi!</b>',
'parse_mode' => 'html'
]);
}

elseif($text == ".online off"){
$config = json_decode(file_get_contents('config.json'));
$config->online = 0;
file_put_contents('config.json', json_encode($config));
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '<b>🖥 24 soat online rejimi oʻchirildi!</b>',
'parse_mode' => 'html'
]);
}





elseif($text == ".police"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🟦🟦🟦🔴🔴🔴🟦🟦🟦',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟥🟥🟥🔵🔵🔵🟥🟥🟥",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🟦🟦🟦🔴🔴🔴🟦🟦🟦",
]);
}

elseif($text == ".love"){
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => '🤍',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍??\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💚💚🤍💚💚🤍🤍\n🤍💚💚💚💚💚💚💚🤍\n🤍💚💚💚💚💚💚💚🤍\n🤍💚💚💚💚💚💚💚🤍\n🤍🤍💚💚💚💚💚🤍🤍\n🤍🤍🤍💚💚💚🤍🤍🤍\n🤍🤍🤍🤍💚🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💛💛🤍💛💛🤍🤍\n🤍💛💛💛💛💛💛💛🤍\n🤍💛💛💛💛💛💛💛🤍\n🤍💛💛💛💛💛💛💛🤍\n🤍🤍💛💛💛💛💛🤍🤍\n🤍🤍🤍💛💛💛🤍🤍🤍\n🤍🤍🤍🤍💛🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💙💙🤍💙💙🤍🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍🤍💙💙💙💙💙🤍🤍\n🤍🤍🤍💙💙💙🤍🤍🤍\n🤍🤍🤍🤍💙🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💜💜🤍💜💜🤍🤍\n🤍💜💜💜💜💜💜💜🤍\n🤍💜💜💜💜💜💜💜🤍\n🤍💜💜💜💜💜💜💜🤍\n🤍🤍💜💜💜💜💜🤍🤍\n🤍🤍🤍💜💜💜🤍🤍🤍\n🤍🤍🤍🤍💜🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍🧡🧡🤍🧡🧡🤍🤍\n🤍🧡🧡🧡🧡🧡🧡🧡🤍\n🤍🧡🧡🧡🧡🧡🧡🧡🤍\n🤍🧡🧡🧡🧡🧡🧡🧡🤍\n🤍🤍🧡🧡🧡🧡🧡🤍🤍\n🤍🤍🤍🧡🧡🧡🤍🤍🤍\n🤍🤍🤍🤍🧡🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍🖤🖤🤍🖤🖤🤍🤍\n🤍🖤🖤🖤🖤🖤🖤🖤🤍\n🤍🖤🖤🖤🖤🖤🖤🖤🤍\n🤍🖤🖤🖤🖤🖤🖤🖤🤍\n🤍🤍🖤🖤🖤🖤🖤🤍🤍\n🤍🤍🤍🖤🖤🖤🤍🤍🤍\n🤍🤍🤍🤍🖤🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💖💖🤍💖💖🤍🤍\n🤍💖💖💖💖💖💖💖🤍\n🤍💖💖💖💖💖💖💖🤍\n🤍💖💖💖💖💖💖💖🤍\n🤍🤍💖💖💖💖💖🤍🤍\n🤍🤍🤍💖💖💖🤍🤍🤍\n🤍🤍🤍🤍💖🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍🧡💛🤍💙💜🤍🤍\n🤍❤️❤️🖤💖🤎💚💛🤍\n🤍💙💜💛💚❤️🤎💙🤍\n🤍💖🤎🖤💖💙💛💖🤍\n🤍🤍💚❤️💜🖤💖🤍🤍\n🤍🤍🤍💖🤎💚🤍🤍🤍\n🤍🤍🤍🤍💛🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💚💙🤍💜💙🤍🤍\n🤍🤎❤️🖤❤️🤎💙💛🤍\n🤍💙💛💚💚❤️🤎💙🤍\n🤍💜🤎🖤🧡💙💛🖤🤍\n🤍🤍🤎❤️💜🖤💚🤍🤍\n🤍🤍🤍💛🤎🖤🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍🧡💛🤍💙💜🤍🤍\n🤍❤️❤️🖤💖🤎💚💛🤍\n🤍💙💜💛💚❤️🤎💙🤍\n🤍💖🤎🖤💖💙💛💖🤍\n🤍🤍💚❤️💜🖤💖🤍🤍\n🤍🤍🤍💖🤎💚🤍🤍🤍\n🤍🤍🤍🤍💛🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍💙💙🤍💙💙🤍🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍💙💙💙💙💙💙💙🤍\n🤍🤍💙💙💙💙💙🤍🤍\n🤍🤍🤍💙💙💙🤍🤍🤍\n🤍🤍🤍🤍💙🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "🤍🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️🤍🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️🤍🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️🤍🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️🤍🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️🤍🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️🤍🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️🤍🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍🤍❤️❤️🤍❤️❤️🤍🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️🤍❤️❤️🤍❤️❤️🤍❤️\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍❤️❤️❤️❤️❤️❤️❤️🤍\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍🤍❤️❤️❤️❤️❤️🤍🤍\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️🤍❤️❤️❤️❤️❤️🤍❤️\n🤍🤍🤍❤️❤️❤️🤍🤍🤍\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️🤍❤️❤️❤️🤍❤️❤️\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n🤍🤍🤍🤍❤️🤍🤍🤍🤍\n🤍🤍🤍🤍🤍🤍🤍🤍🤍",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️❤️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️❤️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️❤️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️\n❤️❤️❤️❤️❤️️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️❤️\n❤️❤️❤️❤️\n❤️❤️❤️❤️\n❤️❤️❤️❤️️️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️❤️\n❤️❤️❤️\n❤️❤️❤️️️️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️❤️\n❤️❤️️️️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "❤️️️️️",
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "<b>I</b>",
'parse_mode'=>'html',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "<b>I ❤️️️️️️️️️️</b>",
'parse_mode'=>'html',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "<b>I ❤ U️️️️️️️️️️</b>",
'parse_mode'=>'html',
]);
usleep(300000);
$this->messages->editMessage([
'peer' => $peer,
'id' => $mid,
'message' => "<b>I ❤️️️️️️️️️️ MOM!</b>",
'parse_mode'=>'html',
]);
exit;
}
}
}

catch(\Throwable $e){
$this->report("Surfaced: $e");
}
}
}


$settings = new Settings;
$settings->getLogger()->setLevel(Logger::LEVEL_ULTRA_VERBOSE);
$settings->getLogger()->setMode(Logger::LOGGER_FILE );
MyEventHandler::startAndLoop('madeline.session', $settings);

///Biz Sizning Harakatingizga javob bermaymiz 
///va qilishga undamaymiz.
///Ushbu Kod @uzb_cristal Tegishli 
/// Old Tarqalgan versiyasi ham bor
///Bu yangilangan versiya 
///Manba : @UzCoder_Kanal


?>