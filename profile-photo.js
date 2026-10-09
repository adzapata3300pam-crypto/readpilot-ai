(function(){
  var OUTPUT_SIZE = 512;
  var ZOOM = 1.2;

  window.createProfilePhotoCrop = async function(file){
    var bitmap = await createImageBitmap(file);
    try {
      var cropSize = Math.min(bitmap.width, bitmap.height) / ZOOM;
      var left = (bitmap.width - cropSize) / 2;
      var top = (bitmap.height - cropSize) / 2;
      var canvas = document.createElement('canvas');
      canvas.width = OUTPUT_SIZE;
      canvas.height = OUTPUT_SIZE;

      var context = canvas.getContext('2d');
      if (!context) throw new Error('Unable to prepare the profile photo crop.');
      context.fillStyle = '#ffffff';
      context.fillRect(0, 0, OUTPUT_SIZE, OUTPUT_SIZE);
      context.drawImage(bitmap, left, top, cropSize, cropSize, 0, 0, OUTPUT_SIZE, OUTPUT_SIZE);

      var blob = await new Promise(function(resolve, reject){
        canvas.toBlob(function(result){
          if (result) resolve(result);
          else reject(new Error('Unable to prepare the profile photo crop.'));
        }, 'image/jpeg', 0.9);
      });
      return new File([blob], 'profile-photo.jpg', {type:'image/jpeg'});
    } finally {
      bitmap.close();
    }
  };
})();
