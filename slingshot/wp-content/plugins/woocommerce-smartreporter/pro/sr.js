proSelectDate = function (dateValue, beta){
	var fromDate,toDate,
		now = new Date();

	if (beta === undefined) beta = "false"; // Initializing beta

	switch (dateValue){

		case 'TODAY':
		fromDate = now;
		toDate 	 = now;
		break;

		case 'YESTERDAY':
		fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
		toDate 	 = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
		break;

		case 'THIS_WEEK':
		fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - (now.getDay() - 1));
		toDate 	 = now;
		break;

		case 'LAST_WEEK':
		fromDate = new Date(now.getFullYear(), now.getMonth(), (now.getDate() - (now.getDay() - 1) - 7));
		toDate   = new Date(now.getFullYear(), now.getMonth(), (now.getDate() - (now.getDay() - 1) - 1));
		break;

		case 'LAST_SEVEN_DAYS':
		fromDate = SR.checkFromDate;
		toDate   = SR.checkToDate;
		break;

		case 'THIS_MONTH':
		fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
		toDate 	 = now;
		break;

		case 'LAST_MONTH':
		fromDate = new Date(now.getFullYear(), now.getMonth()-1, 1);
		toDate   = new Date(now.getFullYear(), now.getMonth(), 0);
		break;

		case '3_MONTHS':
		fromDate = new Date(now.getFullYear(), now.getMonth()-2, 1);
		toDate 	 = now;
		break;

		case '6_MONTHS':
		fromDate = new Date(now.getFullYear(), now.getMonth()-5, 1);
		toDate 	 = now;
		break;

		case 'THIS_YEAR':
		fromDate = new Date(now.getFullYear(), 0, 1);
		toDate 	 = now;
		break;

		case 'LAST_YEAR':
		fromDate = new Date(now.getFullYear() - 1, 0, 1);
		toDate 	 = new Date(now.getFullYear(), 0, 0);
		break;

		case 'LAST_SEVEN_DAYS':
		fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6);
		toDate 	 = now;
		break;

		default:
		fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
		toDate 	 = now;
		break;
	}

	if (beta == "false") {
		SR.fromDateField.setValue(fromDate);
		SR.toDateField.setValue(toDate);	
	}else {
		SR = new Array();
	}
	
	SR.fromDate = fromDate;
	SR.toDate 	= toDate;

	return SR;
};