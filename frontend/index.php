<!DOCTYPE html>
<html>
  <head>
    <title>SpaceCollab DeepLink</title>
    <script
      src="https://code.jquery.com/jquery-3.7.0.min.js"
      integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
      crossorigin="anonymous"
    ></script>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>

  <body class="bg-gray-900">
    <header class="flex flex-row items-center justify-center">
      <ul class="flex flex-wrap text-sm font-medium text-center text-gray-400 border-gray-200 gap-4 my-4">
        <li data-target="desktop-content" class="nav-item flex items-center justify-center">
            <a href="#" class="inline-block p-4 rounded-lg hover:text-gray-300 hover:bg-gray-800 focus:border-4 focus:border-gray-300">Desktop</a>
        </li>
        <li data-target="android-content" class="nav-item flex items-center justify-center">
            <a href="#" class="inline-block p-4 rounded-lg hover:text-gray-300 hover:bg-gray-800 focus:border-4 focus:border-gray-300">Android</a>
        </li>
        <li data-target="vr-content" class="nav-item flex items-center justify-center">
            <a href="#" class="inline-block p-4 rounded-lg hover:text-gray-300 hover:bg-gray-800 focus:border-4 focus:border-gray-300">VR</a>
        </li>
      </ul>
    </header>
    <main class="flex flex-col items-center justify-center w-full h-full" >
      <div class="container py-8 px-4 text-gray-300">
        <div class="content-div hidden" id="desktop-content">
          <!-- Home Content -->
          <div class="flex flex-col items-center gap-2">
            <h2 class="text-2xl">SpaceCollab Desktop App</h2>
            <div class="mb-4">
              <p>This is the content for the Desktop page. Click the link below to redirect to spacecollab desktop app</p>
            </div>
            <a class="p-4 py-2 bg-gray-800 cursor-pointer border rounded-lg" href="<?php echo $deeplink_desktop ?>" target="_blank">Deep Link</a>
          </div>
        </div>
        <div class="content-div hidden" id="android-content">
          <!-- Home Content -->
          <div class="flex flex-col items-center gap-2">
            <h2 class="text-2xl">Android SpaceCollab</h2>
            <div class="mb-4">
              <p>This is the content for the Android page. Click the link below to redirect to spacecollab</p>
            </div>
            <a class="p-4 py-2 bg-gray-800 cursor-pointer border rounded-lg" href="<?php echo $deeplink_android ?>" target="_blank">Deep Link</a>
          </div>
        </div>
        <div class="content-div hidden" id="vr-content">
          <!-- Home Content -->
          <div class="flex flex-col items-center gap-2">
            <h2 class="text-2xl">VR SpaceCollab</h2>
            <div class="mb-4">
              <p>This is the content for the VR page. Click the link below to redirect to spacecollab</p>
            </div>
            <a class="p-4 py-2 bg-gray-800 cursor-pointer border rounded-lg" href="<?php echo $deeplink_vr ?>" target="_blank">Deep Link</a>
          </div>
        </div>
      </div>
    </main>
    <script>
      // Load characters on page load
      $(document).ready(function () {
        console.log("ready!");

        $(".nav-item").click(function() {
          // Remove the "text-blue-600" class from all navigation items
          $(".nav-item").removeClass("text-blue-600");
          // Add the "text-blue-600" class to the clicked navigation item
          $(this).addClass("text-blue-600");

          var targetDivID = $(this).data("target");

          // Hide all content divs
          $(".content-div").addClass("hidden")

          // Show the target content div
          $("#" + targetDivID).removeClass("hidden");
        });
      });
    </script>
  </body>
</html>
