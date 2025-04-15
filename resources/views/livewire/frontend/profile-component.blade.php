<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Profile</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .custom-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: #ffc107;
                color: #fff;
                padding: 5px 10px;
                font-size: 12px;
                border-radius: 50px;
            }
            .currency-icon {
                display: inline-block;
                vertical-align: middle;
                margin-right: 1px;
            }
        </style>
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection

    {{-- <div class="page-content-wrapper">

        <div class="container py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </div>



    </div> --}}

    <div class="page-content-wrapper">
        <div class="container">
          <!-- Profile Wrapper-->
          {{-- <div class="profile-wrapper-area py-3">
            <!-- User Information-->
            <div class="card user-info-card">
              <div class="card-body p-4 d-flex align-items-center">
                <div class="user-profile me-3">
                    <img src="img/bg-img/9.jpg" alt="">
                  <div class="change-user-thumb">
                    <form>
                      <input class="form-control-file" type="file">
                      <button><i class="ti ti-pencil"></i></button>
                    </form>
                  </div>
                </div>
                <div class="user-info">
                  <p class="mb-0 text-white">@designing-world</p>
                  <h5 class="mb-0 text-white">Suha Jannat</h5>
                </div>
              </div>
            </div>
            <!-- User Meta Data-->
            <div class="card user-data-card">
              <div class="card-body">
                <form action="#" method="">
                  <div class="mb-3">
                    <div class="title mb-2"><i class="ti ti-at"></i><span>Username</span></div>
                    <input class="form-control" type="text" value="designing-world">
                  </div>
                  <div class="mb-3">
                    <div class="title mb-2"><i class="ti ti-user"></i><span>Full Name</span></div>
                    <input class="form-control" type="text" value="Suha Jannat" disabled="">
                  </div>
                  <div class="mb-3">
                    <div class="title mb-2"><i class="ti ti-phone"></i><span>Phone</span></div>
                    <input class="form-control" type="text" value="+880 000 111 222">
                  </div>
                  <div class="mb-3">
                    <div class="title mb-2"><i class="ti ti-mail"></i><span>Email Address</span></div>
                    <input class="form-control" type="email" value="care@example.com">
                  </div>
                  <div class="mb-3">
                    <div class="title mb-2"><i class="ti ti-location"></i><span>Shipping Address</span></div>
                    <input class="form-control" type="text" value="28/C Green Road, BD">
                  </div>
                  <button class="btn btn-primary btn-lg w-100" type="submit">Save All Changes</button>
                </form>
              </div>
            </div>
          </div> --}}
          <livewire:profile.update-profile-information-form />
          <livewire:profile.update-password-form />
        </div>
      </div>


    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>
