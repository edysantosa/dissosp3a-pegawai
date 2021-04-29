var mod = require('./mod');

module.exports = {
    //css compile list
	"mainstyle"     : './appresource/javascript/common/style.js',
	//javascript compile list
	"home"               : mod('home'),
	"authentication"     : mod('authentication'),
	"profile"     		 : mod('profile'),
	"user"     		     : mod('user'),
	"user-edit"      	 : mod('user-edit'),
	"import-pegawai"   	 : mod('import-pegawai'),
};