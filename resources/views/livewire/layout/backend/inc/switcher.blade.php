<?php


use Illuminate\Support\Facades\Session;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\AppNotifications;

new class extends Component
{
    public $theme,$headercolor;
public function mount(){
    $theme=auth('admin')->user()->theme;
    $headercolor=auth('admin')->user()->headercolor;
}
}; ?>
<div>
 <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
       <!--end overlay-->

        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->


 <div class="switcher-body">
        <button class="btn btn-primary btn-switcher shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling"><i class="bi bi-paint-bucket me-0"></i></button>
        <div class="offcanvas offcanvas-end shadow border-start-0 p-2" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling">
          <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Theme Customizer</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
          </div>
		  <form action="#" method="POST" enctype="multipart/form-data">
             @csrf
          <div class="offcanvas-body">
            <h6 class="mb-0">Theme Variation</h6>
            <hr>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="theme" id="LightTheme" value="1" {{(@$theme==1)?'checked':''}}>
              <label class="form-check-label" for="LightTheme">Light</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="theme" id="DarkTheme" value="2" {{(@$theme==2)?'checked':''}}>
              <label class="form-check-label" for="DarkTheme">Dark</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="theme" id="SemiDarkTheme" value="3" {{(@$theme==3)?'checked':''}}>
              <label class="form-check-label" for="SemiDarkTheme">Semi Dark</label>
            </div>
            <hr>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="theme" id="MinimalTheme" value="4" {{(@$theme==4)?'checked':''}}>
              <label class="form-check-label" for="MinimalTheme">Minimal Theme</label>
            </div>
            <hr/>
            <h6 class="mb-0">Header Colors</h6>
            <hr/>
            <div class="header-colors-indigators">
              <div class="row row-cols-auto g-3">
			    <div class="col">
                  <div class="indigator headercolor0" id="headercolor0"></div>
				   <input class="form-check-input" type="radio" name="headercolor" id="headercolor0" value="0" {{(@$headercolor==0)?'checked':''}} >
                </div>
                <div class="col">
                  <div class="indigator headercolor1" id="headercolor1"></div>
				   <input class="form-check-input" type="radio" name="headercolor" id="headercolor1" value="1" {{(@$headercolor==1)?'checked':''}} >
                </div>
                <div class="col">
                  <div class="indigator headercolor2" id="headercolor2"></div>
				   <input class="form-check-input" type="radio" name="headercolor" id="headercolor2" value="2" {{(@$headercolor==2)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor3" id="headercolor3"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor3" value="3" {{(@$headercolor==3)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor4" id="headercolor4"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor4" value="4" {{(@$headercolor==4)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor5" id="headercolor5"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor5" value="5" {{(@$headercolor==5)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor6" id="headercolor6"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor6" value="6" {{(@$headercolor==6)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor7" id="headercolor7"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor7" value="7" {{(@$headercolor==7)?'checked':''}}>
                </div>
                <div class="col">
                  <div class="indigator headercolor8" id="headercolor8"></div>
				  <input class="form-check-input" type="radio" name="headercolor" id="headercolor8" value="8" {{(@$headercolor==8)?'checked':''}}>
                </div>
              </div>
            </div>
			<hr/>
            <center>
			 <button type="submit" class="btn btn-success form-control-sm">Save</button>
			</center>
            <hr/>
          </div>
		  </form>
        </div>
       </div>
</div>
