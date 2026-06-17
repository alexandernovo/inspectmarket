<div class="offcanvas offcanvas-end" style="background-color: #343434" data-bs-backdrop="static" tabindex="-1"
    id="staticBackdrop" aria-labelledby="staticBackdropLabel">
    <div class="d-flex justify-content-end mt-3 me-3">
        <button type="button" class="btn btn-closing" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="bi bi-x-lg text-white"></i>
        </button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column justify-content-center align-items-center mt-4">
            <h5 class="offcanvas-title text-white mb-4" style="font-size: 24px" id="staticBackdropLabel">
                Admin Login
            </h5>
            <div class="d-flex gap-2 mb-3">
                <img src="{{ asset('assets/images/logo2.png') }}" class="rounded-circle" alt=""
                    style="width: 120px; height: 120px">
                <img src="{{ asset('assets/images/logo1.png') }}" class="rounded-circle" alt=""
                    style="width: 120px; height: 120px">
            </div>

            <div class="w-100 px-3 mt-4">
                <form id="login_form">
                    <input type="hidden" name="typeLogin" id="typeLogin" value="ADMIN">
                    <div class="form-group mb-2">
                        <label for="" class="mb-1 text-white">Username</label>
                        <div class="position-relative">
                            <i class="bi bi-person-circle position-absolute text-prime"
                                style="top: 50%; cursor: pointer; transform: translateY(-50%); left: 15px"></i>
                            <input type="text" name="username" id="username" class="form-control bg-white"
                                style="text-indent: 20px" placeholder="Username">
                        </div>
                    </div>
                    <div class="form-group mt-4 mb-2">
                        <label for="" class="mb-1 text-white">Password</label>
                        <div class="position-relative">
                            <i class="bi bi-lock-fill position-absolute text-prime"
                                style="top: 50%; cursor: pointer; transform: translateY(-50%); left: 15px"></i>
                            <input type="password" name="password" id="password" class="form-control bg-white"
                                placeholder="Password" style="text-indent: 20px">
                            <i class="bi bi-eye-fill position-absolute toggle-password text-prime" data-target="password"
                                style="top: 50%; cursor: pointer; transform: translateY(-50%); right: 20px"></i>
                        </div>
                        <p id="error_login" class="text-warning mt-1 d-none mb-0 error-class"></p>
                    </div>
                    <a href="{{ route('forgot.password') }}" class="text-white">Forgot Password?</a>
                    <div class="form-group mt-4">
                        <button class="btn btn-prime w-100 text-white" style="font-size: 18px">Log in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
