<!DOCTYPE html>
<html>
<body>

<p>Click on the "Choose File" button to upload a file:</p>

<form action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('post')
    <input type="file" id="images" name="images[]" multiple class="form-control" accept="image/*"
           required>
    <input type="submit">
</form>
<br>
<br>
<br>
<br>
<br>
<a href="{{ route('getFiles') }}">Get multiple images in folder</a>
</body>
</html>
