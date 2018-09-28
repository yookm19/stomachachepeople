<?php
// Composer�ŃC���X�g�[���������C�u�������ꊇ�ǂݍ���
require_once __DIR__ . '/vendor/autoload.php';
// �A�N�Z�X�g�[�N�����g��CurlHTTPClient���C���X�^���X��
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClient�ƃV�[�N���b�g���g��LINEBot���C���X�^���X��
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
// LINE Messaging API�����N�G�X�g�ɕt�^�����������擾
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
// �������������`�F�b�N�B�����ł���΃��N�G�X�g���p�[�X���z���
// �s���ł���Η�O�̓��e���o��
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}
$midFile = __DIR__ . "/files/mids";
// mids�̒��g��ǂݍ���
$mids = explode(PHP_EOL, trim(file_get_contents($midFile)));
// �z��Ɋi�[���ꂽ�e�C�x���g�����[�v�ŏ���
foreach ($events as $event) {
  //  �e�L�X�g���b�Z�[�W�łȂ���Ώ������X�L�b�v
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  // ���b�Z�[�W�𑗂��Ă������[�U�[���擾
  $newMids = array();
  $newMids[] = $event->getUserId();
�@// �V�K�̃��[�U�[�̏ꍇ�͒ǉ�
  $mids = array_merge($newMids, $mids);
  $mids = array_unique($mids);
  file_put_contents($midFile, implode(",", $mids));
  // ���b�Z�[�W��S�o�^���[�U�[ID���Ƀv�b�V��
  foreach ($mids as $mid) {
    $response = $bot->pushMessage($mid, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($event->getText()));
    if (!$response->isSucceeded()) {
      error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
  }
}

?>