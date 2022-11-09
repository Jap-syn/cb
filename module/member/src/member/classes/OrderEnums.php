<?php
namespace member\classes;

class OrderEnums {
	/**
	 * AnotherDeliFlgに「購入者と同じ」を指定する
	 *
	 * @var int
	 */
	const AnotherDeliFlg_SAME_CUSTOMER	= 0;

	/**
	 * AnotherDeliFlgに「別配送先」を指定する
	 *
	 */
	const AnotherDeliFlg_ANOTHER_SPEC	= 1;

	const CloseReason_COMPLETE_NORMALY	= 1;

	const CloseReason_BY_CANCEL			= 2;

	const CloseReason_BY_NG_INCRE		= 3;

	const CloseReason_BY_DAMAGED		= 4;

	const DmiStatus_NG					= -1;

	const DmiStatus_PROCESSING			= 0;

	const DmiStatus_COMPLETED			= 1;


}